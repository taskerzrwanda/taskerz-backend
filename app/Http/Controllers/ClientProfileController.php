<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMyProfileRequest;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{
    public function update(UpdateMyProfileRequest $request)
    {
        $user = auth('api')->user();

        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => $user->fresh(),
        ]);
    }

    public function myTaskRequests(Request $request)
    {
        $user  = auth('api')->user();
        $query = $user->customerRequests()->with('subTask.task');

        if ($request->filled('status') && in_array(
            $request->status,
            ['pending', 'approved', 'completed', 'cancelled'],
            true
        )) {
            $query->where('status', $request->status);
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
}
