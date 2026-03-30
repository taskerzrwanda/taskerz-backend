<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        return response()->json(Service::all(), 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = $request->file('image')->store('services', 'public');

        return Service::create([
            'title' => $request->title,
            'image' => $imagePath
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info('Request Data:', $request->all());
        Log::info('Files:', $request->allFiles());
        
        $service = Service::findOrFail($id);
        
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            
            $imagePath = $request->file('image')->store('services', 'public');
            $service->image = $imagePath;
        }
        
        $service->title = $request->input('title', $service->title);
        $service->save();
        
        return response()->json($service);
    }


    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        Storage::disk('public')->delete($service->image);
        $service->delete();
        return response()->noContent();
    }
}
