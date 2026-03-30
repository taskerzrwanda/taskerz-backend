<?php

namespace App\Http\Controllers;

use App\Models\TaskRequest;
use App\Services\TaskerMatchingService;
use Illuminate\Http\Request;

class TaskerRecommendationController extends Controller
{
    protected $matchingService;

    public function __construct(TaskerMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Get recommended taskers for a task request
     */
    public function getRecommendations($taskRequestId, Request $request)
    {
        $taskRequest = TaskRequest::with(['subTask.task'])->find($taskRequestId);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found'
            ], 404);
        }

        $limit = $request->input('limit', 10);

        $recommendations = $this->matchingService->getTaskerRecommendations(
            $taskRequest,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => [
                'task_request_id' => $taskRequestId,
                'sub_task' => [
                    'id' => $taskRequest->subTask->id,
                    'name' => $taskRequest->subTask->name,
                ],
                'recommendations' => $recommendations,
                'total_found' => count($recommendations)
            ],
            'message' => 'Tasker recommendations retrieved successfully'
        ]);
    }

    /**
     * Get quick match for immediate assignment
     */
    public function quickMatch($taskRequestId)
    {
        $taskRequest = TaskRequest::with(['subTask.task'])->find($taskRequestId);

        if (!$taskRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Task request not found'
            ], 404);
        }

        $recommendations = $this->matchingService->getTaskerRecommendations($taskRequest, 1);

        if (empty($recommendations)) {
            return response()->json([
                'success' => false,
                'message' => 'No suitable taskers found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $recommendations[0],
            'message' => 'Best match found'
        ]);
    }
}
