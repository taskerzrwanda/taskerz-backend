<?php

namespace App\Http\Controllers;

use App\Models\Tasker;
use App\Models\TaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskerDashboardController extends Controller
{
    /**
     * Get tasker's dashboard overview
     */
    public function overview(Request $request)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $stats = [
            'total_assigned' => $tasker->taskRequests()->count(),
            'pending' => $tasker->taskRequests()->where('status', 'approved')->count(),
            'completed' => $tasker->taskRequests()->where('status', 'completed')->count(),
            'cancelled' => $tasker->taskRequests()->where('status', 'cancelled')->count(),
            'rating' => $tasker->rating,
            'completed_tasks_total' => $tasker->completed_tasks
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get tasker's assigned tasks
     */
    public function assignedTasks(Request $request)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = $tasker->taskRequests()->with(['subTask.task']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get pending tasks for tasker
     */
    public function pendingTasks(Request $request)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $tasks = $tasker->taskRequests()
            ->with(['subTask.task'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get completed tasks for tasker
     */
    public function completedTasks(Request $request)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $tasks = $tasker->taskRequests()
            ->with(['subTask.task'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * Get tasker's analytics
     */
    public function analytics(Request $request)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Recent activity (last 30 days)
        $recentActivity = TaskRequest::where('tasker_id', $tasker->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Task completion rate
        $totalAssigned = $tasker->taskRequests()->count();
        $totalCompleted = $tasker->taskRequests()->where('status', 'completed')->count();
        $completionRate = $totalAssigned > 0
            ? round(($totalCompleted / $totalAssigned) * 100, 2)
            : 0;

        // Most common tasks
        $commonTasks = TaskRequest::where('task_requests.tasker_id', $tasker->id)
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
            'data' => [
                'completion_rate' => $completionRate,
                'recent_activity' => $recentActivity,
                'common_tasks' => $commonTasks,
                'total_earnings_estimate' => $this->calculateEstimatedEarnings($tasker)
            ]
        ]);
    }

    /**
     * Calculate estimated earnings (based on completed tasks)
     */
    protected function calculateEstimatedEarnings(Tasker $tasker)
        {
            return (float) TaskRequest::where('task_requests.tasker_id', $tasker->id)
                ->where('task_requests.status', 'completed')
                ->join('sub_tasks', 'task_requests.sub_task_id', '=', 'sub_tasks.id')
                ->sum(DB::raw('COALESCE(sub_tasks.price, 0)'));
        }


    /**
     * Mark task as completed (by tasker)
     */
    public function completeTask(Request $request, $taskRequestId)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $taskRequest = TaskRequest::where('id', $taskRequestId)
            ->where('tasker_id', $tasker->id)
            ->first();

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or not assigned to you'
            ], 404);
        }

        $taskRequest->markAsCompleted();

        return response()->json([
            'success' => true,
            'data' => $taskRequest,
            'message' => 'Task marked as completed'
        ]);
    }
}
