<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = SubTask::with('task');

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

         if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $subTasks = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $subTasks
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
