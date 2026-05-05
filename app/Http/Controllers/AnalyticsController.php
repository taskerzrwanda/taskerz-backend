<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\SubTask;
use App\Models\TaskRequest;
use App\Models\Tasker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard overview
     */
    public function overview()
    {
        $tasks = Task::selectRaw("
            COUNT(*) as total,
            SUM(status = 'active') as active,
            SUM(status = 'inactive') as inactive
        ")->first();

        $subTasks = SubTask::selectRaw("
            COUNT(*) as total,
            SUM(status = 'active') as active,
            SUM(status = 'inactive') as inactive
        ")->first();

        $taskRequests = TaskRequest::selectRaw("
            COUNT(*) as total,
            SUM(status = 'pending') as pending,
            SUM(status = 'approved') as approved,
            SUM(status = 'completed') as completed,
            SUM(status = 'cancelled') as cancelled
        ")->first();

        $taskers = Tasker::selectRaw("
            COUNT(*) as total,
            SUM(status = 'approved') as approved,
            SUM(status = 'pending') as pending,
            SUM(status = 'rejected') as rejected
        ")->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => [
                    'total'    => (int) $tasks->total,
                    'active'   => (int) $tasks->active,
                    'inactive' => (int) $tasks->inactive,
                ],
                'sub_tasks' => [
                    'total'    => (int) $subTasks->total,
                    'active'   => (int) $subTasks->active,
                    'inactive' => (int) $subTasks->inactive,
                ],
                'task_requests' => [
                    'total'     => (int) $taskRequests->total,
                    'pending'   => (int) $taskRequests->pending,
                    'approved'  => (int) $taskRequests->approved,
                    'completed' => (int) $taskRequests->completed,
                    'cancelled' => (int) $taskRequests->cancelled,
                ],
                'taskers' => [
                    'total'    => (int) $taskers->total,
                    'approved' => (int) $taskers->approved,
                    'pending'  => (int) $taskers->pending,
                    'rejected' => (int) $taskers->rejected,
                ],
            ],
        ]);
    }

    /**
     * Get most requested tasks/sub-tasks
     */
   public function mostRequested(Request $request)
{
    $limit = $request->input('limit', 10);

    // Most requested sub-tasks
    $subTasks = SubTask::withCount('taskRequests')
        ->with('task')
        ->orderBy('task_requests_count', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($subTask) {
            return [
                'id' => $subTask->id,
                'name' => $subTask->name,
                'task_name' => $subTask->task->title ?? 'N/A',
                'request_count' => $subTask->task_requests_count
            ];
        });

    // Most requested tasks (aggregated) - FIXED
    $tasks = Task::select('tasks.id', 'tasks.title')
        ->selectRaw('COUNT(task_requests.id) as request_count')
        ->join('sub_tasks', 'tasks.id', '=', 'sub_tasks.task_id')
        ->join('task_requests', 'sub_tasks.id', '=', 'task_requests.sub_task_id')
        ->groupBy('tasks.id', 'tasks.title')
        ->orderBy('request_count', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'request_count' => $task->request_count
            ];
        });

    return response()->json([
        'success' => true,
        'data' => [
            'most_requested_sub_tasks' => $subTasks,
            'most_requested_tasks' => $tasks
        ]
    ]);
}

    /**
     * Get task requests over time
     */
    public function requestsOverTime(Request $request)
    {
        $period = $request->input('period', 'daily'); // daily, weekly, monthly

        $dateFormat = match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $requests = TaskRequest::select(
                DB::raw("DATE_FORMAT(created_at, '$dateFormat') as period"),
                DB::raw('COUNT(*) as count'),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'timeline' => $requests
            ]
        ]);
    }

    /**
     * Get tasker performance metrics
     */
    public function taskerPerformance(Request $request)
    {
        $limit = $request->input('limit', 10);

        $topTaskers = Tasker::select('taskers.*')
            ->where('status', 'approved')
            ->orderBy('completed_tasks', 'desc')
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($tasker) {
                return [
                    'id' => $tasker->id,
                    'name' => $tasker->name,
                    'profession' => $tasker->profession,
                    'completed_tasks' => $tasker->completed_tasks,
                    'rating' => $tasker->rating,
                    'city' => $tasker->city
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'top_performers' => $topTaskers
            ]
        ]);
    }

    /**
     * Get status breakdown
     */
    public function statusBreakdown()
    {
        $breakdown = TaskRequest::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return response()->json([
            'success' => true,
            'data' => [
                'pending' => $breakdown['pending']->count ?? 0,
                'approved' => $breakdown['approved']->count ?? 0,
                'completed' => $breakdown['completed']->count ?? 0,
                'cancelled' => $breakdown['cancelled']->count ?? 0
            ]
        ]);
    }

    /**
     * Get comprehensive analytics report
     */
    public function fullReport()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $this->overview()->getData()->data,
                'most_requested' => $this->mostRequested(new Request())->getData()->data,
                'status_breakdown' => $this->statusBreakdown()->getData()->data,
                'top_performers' => $this->taskerPerformance(new Request())->getData()->data
            ]
        ]);
    }
}
