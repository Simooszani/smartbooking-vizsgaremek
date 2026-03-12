<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Room;

class HotelController extends Controller
{
    public function index()
    {
        return response()->json(Hotel::all(), 200);
    }

    public function search(Request $request)
    {
        $guests = $request->query('guests', 1);
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');
        $destination = $request->query('destination');

        // Hotelek lekérése a kapcsolódó adatokkal (szobák, vélemények)
        $query = Hotel::with(['rooms', 'reviews.user']);

        if ($destination) {
            $query->where('address', 'like', "%$destination%");
        }

        $hotels = $query->get();

        // Szobák szűrése kapacitás és foglaltság alapján
        foreach ($hotels as $hotel) {
            $hotel->rooms = $hotel->rooms->filter(function ($room) use ($guests, $checkIn, $checkOut) {
                // 1. Kapacitás ellenőrzés
                if ($room->capacity < $guests) return false;

                // 2. Dátum átfedés ellenőrzés (ha van megadva dátum)
                if ($checkIn && $checkOut) {
                    $isOccupied = Booking::where('room_id', $room->id)
                        ->where('status', 'confirmed')
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->whereBetween('check_in', [$checkIn, $checkOut])
                              ->orWhereBetween('check_out', [$checkIn, $checkOut])
                              ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                                  $q2->where('check_in', '<=', $checkIn)
                                     ->where('check_out', '>=', $checkOut);
                              });
                        })->exists();
                    
                    if ($isOccupied) return false;
                }
                return true;
            });
        }

        return response()->json($hotels->filter(fn($h) => $h->rooms->count() > 0)->values());
    }

    public function getAllRooms() {
        return response()->json(Room::with('hotel')->get());
    }

    public function storeRoom(Request $request) {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'type' => 'required|string',
            'price_per_night' => 'required|numeric',
            'capacity' => 'required|integer',
            'description' => 'nullable|string'
        ]);

        $room = Room::create($validated);
        return response()->json($room, 201);
    }

    public function updateRoom(Request $request, $id) {
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return response()->json($room);
    }

    public function deleteRoom($id) {
        Room::destroy($id);
        return response()->json(['message' => 'Szoba törölve']);
    }
}