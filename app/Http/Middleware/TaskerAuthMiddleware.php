<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tasker;
use Symfony\Component\HttpFoundation\Response;

class TaskerAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->header('X-Access-Token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Access token required'
            ], 401);
        }

        $tasker = Tasker::where('access_token', $accessToken)->first();

        if (!$tasker) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid access token'
            ], 401);
        }

        // Attach tasker to request for use in controllers
        $request->merge(['authenticated_tasker' => $tasker]);

        return $next($request);
    }
}
