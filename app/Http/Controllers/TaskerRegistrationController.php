<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterTaskerRequest;
use App\Mail\TaskerVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TaskerRegistrationController extends Controller
{
    public function register(RegisterTaskerRequest $request)
    {
        $data = $request->validated();

        $verificationCode = Str::random(6);

        $user = User::create(array_merge($data, [
            'role'              => 'tasker',
            'status'            => 'pending',
            'verification_code' => $verificationCode,
            'email_verified_at' => null,
        ]));

        Mail::to($user->email)->send(new TaskerVerificationMail($verificationCode));

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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tasker not found',
            ], 404);
        }

        $verificationCode = Str::random(6);
        $user->update(['verification_code' => $verificationCode]);
        Mail::to($user->email)->send(new TaskerVerificationMail($verificationCode));

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::taskers()
            ->where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code or email',
            ], 422);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null,
        ]);

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
}
