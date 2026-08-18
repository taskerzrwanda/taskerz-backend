<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary;

class TaskController extends Controller
{
    protected $cloudinary;

    public function __construct()
    {
        // Initialize Cloudinary
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key'    => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    public function index(Request $request)
    {
        $query = Task::with('activeSubTasks');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
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

      public function show($id)
    {
        $task = Task::with('subTasks')->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12288',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Upload image to Cloudinary
            if ($request->hasFile('image')) {
                $uploadResult = $this->cloudinary->uploadApi()->upload(
                    $request->file('image')->getRealPath(),
                    [
                        'folder' => 'taskerz',
                        'resource_type' => 'image'
                    ]
                );

                $data['image'] = $uploadResult['secure_url'];
            }

            $task = Task::create($data);

            return response()->json([
                'success' => true,
                'data' => $task->load('subTasks'),
                'message' => 'Task created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:12288',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            if ($request->hasFile('image')) {
                // Delete old image from Cloudinary
                if ($task->image) {
                    try {
                        $publicId = $this->extractPublicIdFromUrl($task->image);
                        $this->cloudinary->uploadApi()->destroy($publicId);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old image: ' . $e->getMessage());
                    }
                }

                // Upload new image
                $uploadResult = $this->cloudinary->uploadApi()->upload(
                    $request->file('image')->getRealPath(),
                    [
                        'folder' => 'taskerz',
                        'resource_type' => 'image'
                    ]
                );

                $data['image'] = $uploadResult['secure_url'];
            }

            $task->update($data);

            return response()->json([
                'success' => true,
                'data' => $task->load('subTasks'),
                'message' => 'Task updated successfully'
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
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        // Delete image from Cloudinary
        if ($task->image) {
            try {
                $publicId = $this->extractPublicIdFromUrl($task->image);
                $this->cloudinary->uploadApi()->destroy($publicId);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete image from Cloudinary: ' . $e->getMessage());
            }
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $task->update([
            'status' => $task->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Status updated successfully'
        ]);
    }

    /**
     * Extract public_id from Cloudinary URL
     */
    private function extractPublicIdFromUrl($url)
    {
        // Example URL: https://res.cloudinary.com/cloud_name/image/upload/v123456/taskerz/image.jpg
        // We need: taskerz/image

        $parts = explode('/upload/', $url);
        if (count($parts) === 2) {
            $pathParts = explode('/', $parts[1]);
            // Remove version if exists (starts with 'v')
            if (isset($pathParts[0]) && strpos($pathParts[0], 'v') === 0) {
                array_shift($pathParts);
            }
            $publicId = implode('/', $pathParts);
            // Remove file extension
            $publicId = preg_replace('/\.[^.]+$/', '', $publicId);
            return $publicId;
        }

        return $url;
    }
}
