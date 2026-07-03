<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = SubTask::with('task');

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $paginator = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
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
            'task_id' => 'required|exists:tasks,id',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subTask = SubTask::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $subTask->load('task'),
                'message' => 'Sub-task created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'sub_tasks' => 'required|array|min:1|max:50',
            'sub_tasks.*.name' => 'required|string|max:255',
            'sub_tasks.*.price' => 'nullable|numeric|min:0',
            'sub_tasks.*.duration' => 'nullable|string',
            'sub_tasks.*.description' => 'nullable|string',
            'sub_tasks.*.status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taskId = $request->input('task_id');
            $rows = $request->input('sub_tasks');

            $created = DB::transaction(function () use ($taskId, $rows) {
                $models = [];
                foreach ($rows as $row) {
                    $models[] = SubTask::create([
                        'task_id' => $taskId,
                        'name' => $row['name'],
                        'price' => $row['price'] ?? null,
                        'duration' => $row['duration'] ?? null,
                        'description' => $row['description'] ?? null,
                        'status' => $row['status'] ?? 'inactive',
                    ]);
                }
                return $models;
            });

            $data = collect($created)->map(fn ($m) => $m->load('task'))->all();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => count($data) . ' sub-tasks created successfully'
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $subTask = SubTask::with('task')->find($id);

        if (!$subTask) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $subTask
        ]);
    }

    public function update(Request $request, $id)
    {
        $subTask = SubTask::find($id);

        if (!$subTask) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-task not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'task_id' => 'sometimes|exists:tasks,id',
            'name' => 'sometimes|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $subTask->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $subTask->load('task'),
                'message' => 'Sub-task updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $subTask = SubTask::find($id);

        if (!$subTask) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-task not found'
            ], 404);
        }

        $subTask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sub-task deleted successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        $subTask = SubTask::find($id);

        if (!$subTask) {
            return response()->json([
                'success' => false,
                'message' => 'Sub-task not found'
            ], 404);
        }

        $subTask->update([
            'status' => $subTask->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'data' => $subTask,
            'message' => 'Status updated successfully'
        ]);
    }

    public function getByTask($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $subTasks = SubTask::where('task_id', $taskId)
            ->where('status', 'active')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subTasks
        ]);
    }
}
