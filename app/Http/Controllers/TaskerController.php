<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteTaskerRequest;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class TaskerController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    public function index(Request $request)
    {
        $query = User::taskers();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('profession', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['approved', 'pending', 'rejected'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('profession')) {
            $query->where('profession', 'like', '%' . $request->input('profession') . '%');
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->input('district') . '%');
        }

        $paginator = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    public function search(Request $request)
    {
        $search     = $request->query('search');
        $profession = $request->query('profession');
        $minRating  = $request->query('min_rating');
        $district   = $request->query('district');

        if (!$search && !$profession && !$district && !$minRating) {
            return response()->json([
                'message' => 'At least one search parameter is required',
                'taskers' => [],
            ], 200);
        }

        $query = User::approvedTaskers();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('profession', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('skills', 'like', "%{$search}%");
            });
        }

        if ($profession) {
            $query->where('profession', 'like', "%{$profession}%");
        }

        if ($district) {
            $query->where('district', 'like', "%{$district}%");
        }

        if ($minRating && is_numeric($minRating)) {
            $query->where('rating', '>=', $minRating);
        }

        $taskers = $query->latest()->limit(20)->get();

        return response()->json($taskers, 200);
    }

    public function show($id)
    {
        $tasker = User::taskers()->find($id);
        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }
        return response()->json($tasker, 200);
    }

    public function update(Request $request, $id)
    {
        $tasker = User::taskers()->find($id);
        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }

        $payload = $request->except(['role', 'password', 'email', 'verification_code']);
        $tasker->update($payload);

        return response()->json($tasker, 200);
    }

    public function destroy($id)
    {
        $tasker = User::taskers()->find($id);
        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }

        $tasker->delete();
        return response()->json(['message' => 'Tasker deleted'], 200);
    }

    public function approve($id)
    {
        $tasker = User::taskers()->findOrFail($id);

        // Only send the approval email when status actually changes — avoids
        // duplicate emails if an admin clicks approve on an already-approved tasker.
        $wasAlreadyApproved = $tasker->status === 'approved';
        $tasker->update(['status' => 'approved']);

        if (!$wasAlreadyApproved) {
            $this->emails->sendTaskerApproved($tasker);
        }

        return response()->json(['message' => 'Tasker approved', 'tasker' => $tasker]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $tasker = User::taskers()->findOrFail($id);

        $wasAlreadyRejected = $tasker->status === 'rejected';
        $tasker->update(['status' => 'rejected']);

        if (!$wasAlreadyRejected) {
            $this->emails->sendTaskerRejected($tasker, $request->input('reason'));
        }

        return response()->json(['message' => 'Tasker rejected', 'tasker' => $tasker]);
    }

    /**
     * Admin-initiated tasker registration.
     *
     * Creates an approved, email-verified tasker with no password, then emails
     * an invite link backed by the same Password broker token system that
     * POST /auth/reset-password redeems — so the existing reset-password page
     * handles initial password setup with no parallel token infrastructure.
     */
    public function invite(InviteTaskerRequest $request)
    {
        $data = $request->validated();

        $tasker = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'profession'        => $data['profession'],
            'role'              => 'tasker',
            'status'            => 'approved',
            'email_verified_at' => now(),
            'password'          => null,
        ]);

        $token = Password::broker()->createToken($tasker);

        $inviteUrl = sprintf(
            '%s/reset-password?token=%s&email=%s',
            config('notifications.frontend_url'),
            urlencode($token),
            urlencode($tasker->email),
        );

        $this->emails->sendTaskerInvite($tasker, $inviteUrl);

        return response()->json([
            'success' => true,
            'message' => 'Tasker registered. An invite email has been sent.',
            'tasker'  => $tasker,
        ], 201);
    }
}
