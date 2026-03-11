<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HotelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Útvonalak
|--------------------------------------------------------------------------
| Ezek az útvonalak a http://127.0.0.1:8000/api/ alatt érhetőek el.
*/

// --- Publikus API végpontok ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Szállodák (Keresés és megtekintés bejelentkezés nélkül is)
Route::get('/hotels', [HotelController::class, 'index']);
Route::get('/hotels/search', [HotelController::class, 'search']);
Route::get('/hotels/{id}', [HotelController::class, 'show']);

// --- Védett API végpontok (Csak érvényes Bearer tokennel) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Felhasználó adatai és kijelentkezés
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Foglalások (CRUD: index, store, show, update, destroy)
    Route::apiResource('bookings', BookingController::class);
});