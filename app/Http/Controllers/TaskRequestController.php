<?php

namespace App\Http\Controllers;

use App\Models\TaskRequest;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskRequestController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    public function index(Request $request)
    {
        $query = TaskRequest::with(['subTask.task', 'tasker']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('sub_task_id')) {
            $query->where('sub_task_id', $request->sub_task_id);
        }

        if ($request->filled('tasker_id')) {
            $query->where('user_id', $request->tasker_id);
        }

        if ($request->filled('unassigned')) {
            $query->whereNull('user_id');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_task_id' => 'required|exists:sub_tasks,id',
            'full_name'   => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'email'       => 'nullable|email',
            'location'    => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $payload = $validator->validated();

            // Honour the JWT when it belongs to a customer — link the request
            // to their account and source identity fields from the user row so
            // a logged-in user can't impersonate someone else on submission.
            $authUser = auth('api')->user();
            if ($authUser && $authUser->isCustomer()) {
                $payload['customer_id'] = $authUser->id;
                $payload['full_name']   = $authUser->name;
                $payload['email']       = $authUser->email;
                $payload['phone']       = $authUser->phone ?: $payload['phone'];
            }

            $taskRequest = TaskRequest::create($payload);
            $taskRequest->load('subTask');

            $this->emails->sendTaskRequestSubmitted($taskRequest);
            $this->emails->notifyAdminsOfNewTaskRequest($taskRequest);

            return response()->json([
                'success' => true,
                'data'    => $taskRequest,
                'message' => 'Request submitted successfully',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Task request creation failed', [
                'error'   => $e->getMessage(),
                'payload' => $request->except(['password']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not submit your request. Please try again shortly.',
            ], 500);
        }
    }

    public function show($id)
    {
        $taskRequest = TaskRequest::with(['subTask.task', 'tasker'])->find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $taskRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status'      => 'sometimes|in:pending,approved,cancelled,completed',
            'description' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $taskRequest->update($validator->validated());

        return response()->json([
            'success' => true,
            'data'    => $taskRequest->load(['subTask', 'tasker']),
            'message' => 'Task request updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
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
            'tasker_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('users', 'id')->where('role', 'tasker'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        $taskRequest->assignToTasker($request->tasker_id);
        $taskRequest->load(['subTask', 'tasker']);

        $this->emails->sendTaskerAssigned($taskRequest);
        $this->emails->sendRequesterAssigned($taskRequest);

        return response()->json([
            'success' => true,
            'data'    => $taskRequest,
            'message' => 'Tasker assigned successfully',
        ]);
    }

    /**
     * Admin-only approval without assignment — moves status pending → approved
     * so a tasker can be matched later. Customer gets a heads-up email.
     */
    public function approve($id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        $taskRequest->update(['status' => 'approved']);
        $taskRequest->load(['subTask', 'tasker']);

        $this->emails->sendTaskRequestApproved($taskRequest);

        return response()->json([
            'success' => true,
            'data'    => $taskRequest,
            'message' => 'Task request approved',
        ]);
    }

    /**
     * Admin-driven rejection. Persists as status=cancelled (the schema enum
     * has no 'rejected' value) but sends a "your request was cancelled"
     * email with an optional reason.
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        $taskRequest->cancel();
        $taskRequest->load(['subTask', 'tasker']);

        $this->emails->sendTaskRequestCancelled($taskRequest, $request->input('reason'));

        return response()->json([
            'success' => true,
            'data'    => $taskRequest,
            'message' => 'Task request rejected',
        ]);
    }

    public function complete($id)
    {
        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        $taskRequest->markAsCompleted();
        $taskRequest->load(['subTask', 'tasker']);

        $this->emails->sendTaskRequestCompleted($taskRequest);

        return response()->json([
            'success' => true,
            'data'    => $taskRequest,
            'message' => 'Task marked as completed',
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $taskRequest = TaskRequest::find($id);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found',
            ], 404);
        }

        $taskRequest->cancel();
        $taskRequest->load(['subTask', 'tasker']);

        $this->emails->sendTaskRequestCancelled($taskRequest, $request->input('reason'));

        return response()->json([
            'success' => true,
            'data'    => $taskRequest,
            'message' => 'Task request cancelled',
        ]);
    }
}
