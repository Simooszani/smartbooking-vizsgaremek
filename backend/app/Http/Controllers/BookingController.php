<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $room = \App\Models\Room::findOrFail($request->room_id);

        if ($request->guests > $room->capacity) {
            return response()->json([
                'message' => "Sajnos ez a szoba túl kicsi! Maximum {$room->capacity} fő fér el, de te {$request->guests} főre foglalnál."
            ], 422);
        }

        $overlap = Booking::where('room_id', $request->room_id)
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

    public function destroy($id) {
        $booking = Booking::findOrFail($id);
        
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Bejelentkezés szükséges!'], 401);
        }

        $isAdmin = (bool) $user->is_admin; 

        if (!$isAdmin && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Nincs jogosultságod!'], 403);
        }

        $booking->delete();
        return response()->json(['message' => 'Foglalás törölve']);
    }

    public function allBookings() {
        return Booking::with(['user', 'room.hotel'])->get();
    }
}