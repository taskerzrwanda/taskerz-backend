<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    // -----------------------------------------------------------------------
    // Standard auth
    // -----------------------------------------------------------------------

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password are wrong',
            ], 401);
        }

        $user = auth('api')->user();

        // Customers + taskers must verify their email before getting a token.
        // Admins skip the gate (seeded accounts have no verification step).
        if (!$user->isAdmin() && is_null($user->email_verified_at)) {
            auth('api')->logout();

            // Auto-issue a fresh code so the user lands on verify-code ready to act.
            $this->issueVerificationCode($user);

            return response()->json([
                'success'               => false,
                'message'               => 'Please verify your email before logging in.',
                'requires_verification' => true,
                'email'                 => $user->email,
            ], 403);
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * Customer self-registration. Creates a pending user, emails a verification
     * code, and returns the user record WITHOUT a token — the client must call
     * /auth/verify-code with the emailed code to log in.
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => $data['password'],
            'role'              => 'user',
            'email_verified_at' => null,
        ]);

        $this->issueVerificationCode($user);

        return response()->json([
            'success'               => true,
            'message'               => 'Account created. Check your email for a verification code.',
            'user'                  => $user,
            'requires_verification' => true,
        ], 201);
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'user'    => auth('api')->user(),
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ]);
    }

    public function refresh()
    {
        $token = auth('api')->refresh();
        return $this->respondWithToken($token, auth('api')->user());
    }

    // -----------------------------------------------------------------------
    // Email verification (generic — works for any role)
    // -----------------------------------------------------------------------

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !hash_equals((string) $user->verification_code, (string) $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code or email',
            ], 422);
        }

        $ttl = (int) config('notifications.verification_code_ttl_minutes');
        if ($ttl > 0 && $user->verification_code_sent_at
            && $user->verification_code_sent_at->lt(now()->subMinutes($ttl))) {
            return response()->json([
                'success'         => false,
                'message'         => 'Verification code expired. Please request a new one.',
                'expired'         => true,
                'email'           => $user->email,
            ], 422);
        }

        $isFirstVerification = is_null($user->email_verified_at);

        $user->forceFill([
            'email_verified_at'         => now(),
            'verification_code'         => null,
            'verification_code_sent_at' => null,
        ])->save();

        // Welcome email is a one-time event, only on the FIRST verification.
        if ($isFirstVerification) {
            if ($user->isTasker()) {
                $this->emails->sendTaskerWelcome($user);
            } elseif ($user->isCustomer()) {
                $this->emails->sendCustomerWelcome($user);
            }
        }

        $token = auth('api')->login($user);
        return $this->respondWithToken($token, $user);
    }

    public function requestVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // We always return 200 to avoid leaking whether the email exists.
        if ($user && is_null($user->email_verified_at)) {
            $this->issueVerificationCode($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for that email, a verification code has been sent.',
        ]);
    }

    // -----------------------------------------------------------------------
    // Password reset
    // -----------------------------------------------------------------------

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Password::sendResetLink calls $user->sendPasswordResetNotification($token),
        // which we override on User to route through EmailNotificationService.
        Password::broker()->sendResetLink(['email' => $request->email]);

        // Always 200 — never reveal whether an account exists.
        return response()->json([
            'success' => true,
            'message' => 'If an account exists for that email, a reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => ['required', 'confirmed', PasswordRule::min(8)->letters()],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => $this->passwordResetErrorMessage($status),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now log in.',
        ]);
    }

    // -----------------------------------------------------------------------
    // Profile (existing)
    // -----------------------------------------------------------------------

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'user'    => $user,
            'message' => 'Profile updated successfully',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    /**
     * Generate a 6-digit numeric code, persist it on the user, and queue
     * a verification email. Numeric-only matches the frontend "000000" input.
     */
    private function issueVerificationCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'verification_code'         => $code,
            'verification_code_sent_at' => now(),
        ])->save();

        $this->emails->sendVerificationCode($user, $code);
    }

    private function passwordResetErrorMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'This reset link is invalid or has expired.',
            Password::INVALID_USER  => 'This reset link is invalid or has expired.',
            Password::RESET_THROTTLED => 'Too many reset attempts. Please wait and try again.',
            default => 'Unable to reset password. Please request a new reset link.',
        };
    }

    protected function respondWithToken(string $token, User $user, int $code = 200)
    {
        return response()->json([
            'success'    => true,
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], $code);
    }
}
