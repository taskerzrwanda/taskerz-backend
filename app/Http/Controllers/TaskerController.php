<?php

namespace App\Http\Controllers;

use App\Models\Tasker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskerVerificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TaskerController extends Controller
{
    //
    // Get all taskers
      public function index(Request $request)
    {
        // Only return taskers if explicitly requested via query param
        $getAll = $request->query('get_all', false);

        if ($getAll) {
            return response()->json(Tasker::latest()->get(), 200);
        }

        // Return empty array by default - no automatic data fetching
        return response()->json([], 200);
    }

    public function search(Request $request)
    {
        $search = $request->query('search');
        $profession = $request->query('profession');
        $minRating = $request->query('min_rating');
        $district = $request->query('district');

        // Require at least one search parameter
        if (!$search && !$profession && !$district && !$minRating) {
            return response()->json([
                'message' => 'At least one search parameter is required',
                'taskers' => []
            ], 200);
        }

        // Start query - only approved taskers
        $query = Tasker::where('status', 'approved');

        // Search by multiple fields
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('profession', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('skills', 'like', "%{$search}%");
            });
        }

        // Filter by profession
        if ($profession) {
            $query->where('profession', 'like', "%{$profession}%");
        }

        // Filter by district
        if ($district) {
            $query->where('district', 'like', "%{$district}%");
        }

        // Filter by minimum rating
        if ($minRating && is_numeric($minRating)) {
            $query->where('rating', '>=', $minRating);
        }

        // Get paginated results
        $taskers = $query->latest()
                        ->limit(20) // Limit results for performance
                        ->get();

        return response()->json($taskers, 200);
    }

    // Store a new tasker
    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nationality' => 'required|string',
            'gender' => 'required|string',
            'education' => 'required|string',
            'email' => 'required|email|unique:taskers',
            'phone' => 'required|string',
            'profession' => 'required|string',
            'work_experience' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tasker = Tasker::create($request->all());


        $verificationCode = Str::random(6);
        $tasker->update(['verification_code' => $verificationCode]);
        Mail::to($tasker->email)->send(new TaskerVerificationMail($verificationCode));

        return response()->json([
            'message' => 'Tasker created successfully',
            'tasker' => $tasker
        ],201);

    }


    // Get single tasker
    public function show($id)
    {
        $tasker = Tasker::find($id);
        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }
        return response()->json($tasker, 200);
    }


    // Update tasker
    public function update(Request $request, $id)
    {
        $tasker = Tasker::find($id);
        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }

        $tasker->update($request->all());
        return response()->json($tasker, 200);
    }


    // Delete tasker
    public function destroy($id)
    {
        $tasker = Tasker::find($id);
        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }

        $tasker->delete();
        return response()->json(['message' => 'Tasker deleted'], 200);
    }



    //    Verificaftion

    public function requestVerification(Request $request)
    {

        $request->validate(['email' => 'required|email']);

        $tasker = Tasker::where('email', $request->email)->first();


        // check if tasker instance exists

        if (!$tasker) {
            return response()->json(['message' => 'Tasker not found'], 404);
        }



        $verificationCode = Str::random(6);
        $tasker->update(['verification_code' => $verificationCode]);
        Mail::to($tasker->email)->send(new TaskerVerificationMail($verificationCode));

        return response()->json([
            'message' => 'Verification code sent',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'email' => 'required|email'
        ]);

        $tasker = Tasker::where([
            'email' => $request->email,
            'verification_code' => $request->code
        ])->firstOrFail();


        // Check if the tasker verification code is correct
        if (!$tasker) {
            return response()->json(['message' => 'Invalid verification code or email'], 422);
        }


        // Generate access token
        $accessToken = Str::uuid();
        $tasker->update(['access_token' => $accessToken]);

        return response()->json([
            'message' => 'Tasker verified',
            'access_token' => $accessToken,
            'tasker' => $tasker
        ]);
    }

    public function getTasker(Request $request)
    {
        $accessToken = $request->header('X-Access-Token');

        $tasker = Tasker::where('access_token', $accessToken)
            ->firstOrFail();

        return $tasker;
    }

    public function updates(Request $request, Tasker $tasker)
    {
        // $accessToken = $request->header('X-Access-Token');

        // if ($tasker->access_token !== $accessToken) {
        //     abort(403, 'Unauthorized');
        // }

        $tasker->update($request->all());

        return response()->json($tasker);
    }



    public function approve($id)
    {
        $tasker = Tasker::findOrFail($id);
        $tasker->update(['status' => 'approved']);
        return response()->json(['message' => 'Tasker approved', 'tasker' => $tasker]);
    }

    public function reject($id)
    {
        $tasker = Tasker::findOrFail($id);
        $tasker->update(['status' => 'rejected']);
        return response()->json(['message' => 'Tasker rejected', 'tasker' => $tasker]);
    }
}
