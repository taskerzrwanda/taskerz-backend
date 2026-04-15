<?php

namespace App\Http\Controllers;

use App\Mail\TaskerEmailSender;
use App\Models\TaskRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TaskRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskRequest::with(['subTask.task', 'tasker']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('sub_task_id')) {
            $query->where('sub_task_id', $request->sub_task_id);
        }

        if ($request->has('tasker_id')) {
            $query->where('tasker_id', $request->tasker_id);
        }

        if ($request->has('unassigned')) {
            $query->whereNull('tasker_id');
        }

        $requests = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_task_id' => 'required|exists:sub_tasks,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $taskRequest = TaskRequest::create($request->all());
            $taskRequest->load('subTask');

            // $admin = User::where('role', 'admin')->first();

            // if ($admin) {
            //     try {
            //         Mail::to($admin->email)->send(new TaskerEmailSender($taskRequest, 'admin'));
            //     } catch (\Exception $mailError) {
            //         \Log::error('Admin email failed: ' . $mailError->getMessage());
            //     }
            // }

            // if ($request->email) {
            //     try {
            //         Mail::to($request->email)->send(new TaskerEmailSender($taskRequest, 'requester'));
            //     } catch (\Exception $mailError) {
            //         \Log::error('User email failed: ' . $mailError->getMessage());
            //     }
            // }

            return response()->json(
                [
                    'success' => true,
                    'data' => $taskRequest,
                    'message' => 'Request submitted successfully',
                ],
                201,
            );
        } catch (\Exception $e) {
            \Log::error('Task Request Error: ' . $e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function show($id)
    {
        $taskRequest = TaskRequest::with(['subTask.task', 'tasker'])->find($id);

        if (!$taskRequest) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Task request not found',
                ],
                404,
            );
        }

        return response()->json([
            'success' => true,
            'data' => $taskRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Task request not found',
                ],
                404,
            );
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,approved,cancelled,completed',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $taskRequest->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $taskRequest->load(['subTask', 'tasker']),
                'message' => 'Task request updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function destroy($id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Task request not found',
                ],
                404,
            );
        }

        $taskRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully',
        ]);
    }

    public function assignTasker(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tasker_id' => 'required|exists:taskers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Task request not found',
                ],
                404,
            );
        }

        try {
            $taskRequest->assignToTasker($request->tasker_id);

            // Load relationships for email
            $taskRequest->load(['subTask', 'tasker']);

            // Send email notification to the assigned tasker
            if ($taskRequest->tasker && $taskRequest->tasker->email) {
                Mail::to($taskRequest->tasker->email)->send(new TaskerEmailSender($taskRequest, 'tasker'));
            }

            // Optional: Also send notification to requester about assignment
            if ($taskRequest->email) {
                Mail::to($taskRequest->email)->send(new TaskerEmailSender($taskRequest, 'requester_assigned'));
            }

            return response()->json([
                'success' => true,
                'data' => $taskRequest,
                'message' => 'Tasker assigned successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Tasker Assignment Error: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function complete($id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Task request not found',
                ],
                404,
            );
        }

        try {
            $taskRequest->markAsCompleted();

            return response()->json([
                'success' => true,
                'data' => $taskRequest->load(['subTask', 'tasker']),
                'message' => 'Task marked as completed',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function cancel($id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Task request not found',
                ],
                404,
            );
        }

        try {
            $taskRequest->cancel();

            return response()->json([
                'success' => true,
                'data' => $taskRequest->load(['subTask', 'tasker']),
                'message' => 'Task request cancelled',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }
}
