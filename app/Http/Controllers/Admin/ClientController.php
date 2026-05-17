<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClientRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::customers();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $query->withCount([
            'taskRequests as total_requests',
            'completedTaskRequests as completed_requests',
        ]);

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

    public function show($id)
    {
        $client = User::customers()
            ->withCount([
                'taskRequests as total_requests',
                'completedTaskRequests as completed_requests',
            ])
            ->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $client->load(['taskRequests' => function ($q) {
            $q->with('subTask.task')->latest()->limit(20);
        }]);

        return response()->json([
            'success' => true,
            'data'    => $client,
        ]);
    }

    public function update($id, UpdateClientRequest $request)
    {
        $client = User::customers()->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $client->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            'data'    => $client->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $client = User::customers()->find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client removed successfully',
        ]);
    }
}
