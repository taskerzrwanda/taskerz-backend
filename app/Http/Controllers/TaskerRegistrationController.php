<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTaskerRequest;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;

class TaskerRegistrationController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    public function register(RegisterTaskerRequest $request)
    {
        $data = $request->validated();

        $user = User::create(array_merge($data, [
            'role'              => 'tasker',
            'status'            => 'pending',
            'email_verified_at' => null,
        ]));

        $this->issueCode($user);

        return response()->json([
            'success' => true,
            'user'    => $user,
            'message' => 'Verification code sent to email',
        ], 201);
    }

    public function requestVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::taskers()->where('email', $request->email)->first();

        // Always 200 regardless of whether a tasker matched — no email enumeration.
        if ($user && is_null($user->email_verified_at)) {
            $this->issueCode($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for that email, a verification code has been sent.',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::taskers()->where('email', $request->email)->first();

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
                'success' => false,
                'message' => 'Verification code expired. Please request a new one.',
                'expired' => true,
                'email'   => $user->email,
            ], 422);
        }

        $isFirstVerification = is_null($user->email_verified_at);

        $user->forceFill([
            'email_verified_at'         => now(),
            'verification_code'         => null,
            'verification_code_sent_at' => null,
        ])->save();

        if ($isFirstVerification) {
            $this->emails->sendTaskerWelcome($user);
        }

        $token = auth('api')->login($user);

        return response()->json([
            'success'    => true,
            'message'    => 'Tasker verified',
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    private function issueCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'verification_code'         => $code,
            'verification_code_sent_at' => now(),
        ])->save();

        $this->emails->sendVerificationCode($user, $code);
    }
}
