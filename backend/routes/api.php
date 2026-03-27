<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HotelAdminMiddleware;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/hotels', [HotelController::class, 'index']);
Route::get('/hotels/search', [HotelController::class, 'search']);
Route::get('/hotels/{id}', [HotelController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    Route::post('/reviews', [ReviewController::class, 'store']);

    // Értesítések (vendég)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Hotel Admin végpontok
    Route::middleware([HotelAdminMiddleware::class])->group(function () {
        Route::get('/hotel-admin/bookings', [BookingController::class, 'hotelBookings']);
    });

    // Admin végpontok
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/admin/bookings', [BookingController::class, 'allBookings']);
        Route::get('/admin/users', [AuthController::class, 'getAllUsers']);
        Route::delete('/admin/users/{id}', [AuthController::class, 'deleteUser']);
        Route::put('/admin/users/{id}/role', [AuthController::class, 'updateUserRole']);

        Route::get('/admin/rooms', [HotelController::class, 'getAllRooms']);
        Route::post('/admin/rooms', [HotelController::class, 'storeRoom']);
        Route::put('/admin/rooms/{id}', [HotelController::class, 'updateRoom']);
        Route::delete('/admin/rooms/{id}', [HotelController::class, 'deleteRoom']);

        Route::post('/admin/hotels', [HotelController::class, 'store']);
        Route::put('/admin/hotels/{id}', [HotelController::class, 'update']);
        Route::delete('/admin/hotels/{id}', [HotelController::class, 'destroy']);
    });
});
