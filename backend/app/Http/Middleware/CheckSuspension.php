<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuspension
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isSuspended()) {
            return response()->json([
                'message' => 'A fiókod fel van függesztve!',
                'suspended_until' => $user->suspended_until->toISOString(),
            ], 403);
        }

        return $next($request);
    }
}
