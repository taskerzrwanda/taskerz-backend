<?php

namespace App\Http\Controllers;

use App\Models\TaskRequest;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskerDashboardController extends Controller
{
    public function __construct(private readonly EmailNotificationService $emails) {}

    public function overview(Request $request)
    {
        $tasker = auth('api')->user();

        $stats = [
            'total_assigned'        => $tasker->taskRequests()->count(),
            'pending'               => $tasker->taskRequests()->where('status', 'approved')->count(),
            'completed'             => $tasker->taskRequests()->where('status', 'completed')->count(),
            'cancelled'             => $tasker->taskRequests()->where('status', 'cancelled')->count(),
            'rating'                => $tasker->rating,
            'completed_tasks_total' => $tasker->completed_tasks,
        ];

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    public function assignedTasks(Request $request)
    {
        $tasker = auth('api')->user();

        $query = $tasker->taskRequests()->with(['subTask.task']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks,
        ]);
    }

    public function pendingTasks(Request $request)
    {
        $tasker = auth('api')->user();

        $tasks = $tasker->taskRequests()
            ->with(['subTask.task'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks,
        ]);
    }

    public function completedTasks(Request $request)
    {
        $tasker = auth('api')->user();

        $tasks = $tasker->taskRequests()
            ->with(['subTask.task'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks,
        ]);
    }

    public function analytics(Request $request)
    {
        $tasker = auth('api')->user();

        $recentActivity = TaskRequest::where('user_id', $tasker->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalAssigned  = $tasker->taskRequests()->count();
        $totalCompleted = $tasker->taskRequests()->where('status', 'completed')->count();
        $completionRate = $totalAssigned > 0
            ? round(($totalCompleted / $totalAssigned) * 100, 2)
            : 0;

        $commonTasks = TaskRequest::where('task_requests.user_id', $tasker->id)
            ->join('sub_tasks', 'task_requests.sub_task_id', '=', 'sub_tasks.id')
            ->select(
                'sub_tasks.name as sub_task_name',
                DB::raw('COUNT(task_requests.id) as count')
            )
            ->groupBy('task_requests.sub_task_id', 'sub_tasks.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'completion_rate'         => $completionRate,
                'recent_activity'         => $recentActivity,
                'common_tasks'            => $commonTasks,
                'total_earnings_estimate' => $this->calculateEstimatedEarnings($tasker),
            ],
        ]);
    }

    protected function calculateEstimatedEarnings(User $tasker)
    {
        return (float) TaskRequest::where('task_requests.user_id', $tasker->id)
            ->where('task_requests.status', 'completed')
            ->join('sub_tasks', 'task_requests.sub_task_id', '=', 'sub_tasks.id')
            ->sum(DB::raw('COALESCE(sub_tasks.price, 0)'));
    }

    public function completeTask(Request $request, $taskRequestId)
    {
        $tasker = auth('api')->user();

        $taskRequest = TaskRequest::where('id', $taskRequestId)
            ->where('user_id', $tasker->id)
            ->first();

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not assigned to you',
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
}
