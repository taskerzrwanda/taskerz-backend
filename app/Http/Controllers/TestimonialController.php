<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class TestimonialController extends Controller
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Testimonial::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'quote' => 'required|string',
            'author_name' => 'required|string',
            'author_title' => 'required|string',
            'company' => 'required|string',
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:307200'
        ]);

        try {
            $file = $request->file('media');
            $mimeType = $file->getMimeType();
            $mediaType = str_starts_with($mimeType, 'video') ? 'video' : 'image';

            // Upload to Cloudinary
            $uploadResult = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => 'testimonials',
                    'resource_type' => $mediaType
                ]
            );

            $testimonial = Testimonial::create([
                ...$request->only('quote', 'author_name', 'author_title', 'company'),
                'media_type' => $mediaType,
                'media_path' => $uploadResult['secure_url']
            ]);

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Testimonial added successfully'
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
        $testimonial = Testimonial::findOrFail($id);

        $request->validate([
            'quote' => 'sometimes|string',
            'author_name' => 'sometimes|string',
            'author_title' => 'sometimes|string',
            'company' => 'sometimes|string',
            'media' => 'sometimes|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:307200'
        ]);

        try {
            if ($request->hasFile('media')) {
                // Delete old media from Cloudinary
                if ($testimonial->media_path) {
                    try {
                        $publicId = $this->extractPublicIdFromUrl($testimonial->media_path);
                        $resourceType = $testimonial->media_type === 'video' ? 'video' : 'image';
                        $this->cloudinary->uploadApi()->destroy($publicId, ['resource_type' => $resourceType]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old media: ' . $e->getMessage());
                    }
                }

                // Upload new media
                $file = $request->file('media');
                $mimeType = $file->getMimeType();
                $mediaType = str_starts_with($mimeType, 'video') ? 'video' : 'image';

                $uploadResult = $this->cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    [
                        'folder' => 'testimonials',
                        'resource_type' => $mediaType
                    ]
                );

                $testimonial->update([
                    'media_type' => $mediaType,
                    'media_path' => $uploadResult['secure_url']
                ]);
            }

            // Update other fields
            $testimonial->update($request->only('quote', 'author_name', 'author_title', 'company'));

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Testimonial updated successfully'
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
        $testimonial = Testimonial::findOrFail($id);

        try {
            // Delete media from Cloudinary
            if ($testimonial->media_path) {
                try {
                    $publicId = $this->extractPublicIdFromUrl($testimonial->media_path);
                    $resourceType = $testimonial->media_type === 'video' ? 'video' : 'image';
                    $this->cloudinary->uploadApi()->destroy($publicId, ['resource_type' => $resourceType]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete media from Cloudinary: ' . $e->getMessage());
                }
            }

            $testimonial->delete();

            return response()->json([
                'success' => true,
                'message' => 'Testimonial deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract public_id from Cloudinary URL
     */
    private function extractPublicIdFromUrl($url)
    {

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
