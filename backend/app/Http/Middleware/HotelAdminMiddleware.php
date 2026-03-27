<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HotelAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && in_array(Auth::user()->role, ['hotel_admin', 'admin', 'super_admin'])) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Hozzáférés megtagadva.'
        ], 403);
    }
}
