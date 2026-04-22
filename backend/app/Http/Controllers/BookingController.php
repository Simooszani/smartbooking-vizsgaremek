<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Sima user saját foglalásai
    public function index()
    {
        return Booking::with(['room.hotel'])->where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:yesterday',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        if ($checkIn->diffInDays($checkOut) > 31) {
            return response()->json([
                'message' => 'Maximum 31 éjszakára lehet foglalni!'
            ], 422);
        }

        $room = \App\Models\Room::findOrFail($request->room_id);

        if ($request->guests > $room->capacity) {
            return response()->json([
                'message' => "Sajnos ez a szoba túl kicsi! Maximum {$room->capacity} fő fér el, de te {$request->guests} főre foglalnál."
            ], 422);
        }

        $overlap = Booking::where('room_id', $request->room_id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($request) {
                $query->where('check_in', '<', $request->check_out)
                    ->where('check_out', '>', $request->check_in);
            })->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Ez a szoba a választott időpontban már foglalt!'
            ], 422);
        }

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'room_id' => $request->room_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'guests' => $request->guests,
            'status' => 'confirmed'
        ]);

        return response()->json($booking, 201);
    }

    // Foglalás törlése (user: saját, admin: bármi, hotel_admin: saját hotel)
    public function destroy(Request $request, $id)
    {
        $booking = Booking::with('room.hotel')->findOrFail($id);
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Bejelentkezés szükséges!'], 401);
        }

        $isAdmin = in_array($user->role, ['admin', 'super_admin']);
        $isHotelAdmin = $user->role === 'hotel_admin'
            && $user->managed_hotel_id
            && $booking->room
            && $booking->room->hotel_id === $user->managed_hotel_id;

        if (!$isAdmin && !$isHotelAdmin && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Nincs jogosultságod!'], 403);
        }

        // Ha admin vagy hotel admin törli, értesítés küldése indoklással
        if (($isAdmin || $isHotelAdmin) && $booking->user_id !== $user->id) {
            $reason = $request->input('reason', 'Nincs megadva indok.');
            $hotelName = $booking->room && $booking->room->hotel
                ? $booking->room->hotel->name
                : 'Ismeretlen szálloda';

            Notification::create([
                'user_id' => $booking->user_id,
                'hotel_id' => $booking->room ? $booking->room->hotel_id : null,
                'booking_id' => $booking->id,
                'type' => 'booking_cancelled',
                'message' => "A foglalásod a(z) {$hotelName} szállodában törlésre került.|||{$reason}",
            ]);
        }

        $booking->delete();
        return response()->json(['message' => 'Foglalás törölve']);
    }

    // Admin: összes foglalás
    public function allBookings()
    {
        return Booking::with(['user', 'room.hotel'])->get();
    }

    // Hotel Admin: saját hotel foglalásai
    public function hotelBookings()
    {
        $user = Auth::user();

        if (!$user->managed_hotel_id) {
            return response()->json(['message' => 'Nincs hozzárendelt szálloda!'], 403);
        }

        $bookings = Booking::with(['user', 'room.hotel'])
            ->whereHas('room', function ($q) use ($user) {
                $q->where('hotel_id', $user->managed_hotel_id);
            })
            ->get();

        return response()->json($bookings);
    }
}
