<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Faq::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:1000'
        ]);

        $faq = Faq::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $faq,
            'message' => 'FAQ created successfully'
        ], 201);
    }

    public function update(Request $request,$id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'sometimes|string|max:500',
            'answer' => 'sometimes|string|max:1000'
        ]);

        $faq->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $faq,
            'message' => 'FAQ updated successfully'
        ]);
    }


    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();
        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully'
        ]);
    }
}
