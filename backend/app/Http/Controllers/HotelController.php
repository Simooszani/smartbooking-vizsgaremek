<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index()
    {
        return response()->json(Hotel::all(), 200);
    }

    public function show($id)
    {
        $hotel = Hotel::with(['rooms', 'reviews.user'])->find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel nem található'], 404);
        }
        return response()->json($hotel);
    }

    public function search(Request $request)
    {
        $guests = $request->query('guests', 1);
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');
        $destination = $request->query('destination');

        $query = Hotel::with(['rooms', 'reviews.user']);

        if ($destination) {
            $query->where('address', 'like', "%$destination%");
        }

        $hotels = $query->get();

        foreach ($hotels as $hotel) {
            $hotel->rooms = $hotel->rooms->filter(function ($room) use ($guests, $checkIn, $checkOut) {
                if ($room->capacity < $guests) return false;

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

    // Admin: Hotel létrehozás
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $validated['rating'] = 0;

        $hotel = Hotel::create($validated);
        return response()->json($hotel, 201);
    }

    // Admin: Hotel frissítés
    public function update(Request $request, $id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel nem található'], 404);
        }

        $hotel->update($request->only(['name', 'address', 'description', 'image']));
        return response()->json($hotel);
    }

    // Admin: Hotel törlés
    public function destroy($id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return response()->json(['message' => 'Hotel nem található'], 404);
        }

        $hotel->rooms()->delete();
        $hotel->reviews()->delete();
        $hotel->delete();

        return response()->json(['message' => 'Hotel sikeresen törölve']);
    }

    public function getAllRooms()
    {
        return response()->json(Room::with('hotel')->get());
    }

    public function storeRoom(Request $request)
    {
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

    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->update($request->all());
        return response()->json($room);
    }

    public function deleteRoom($id)
    {
        Room::destroy($id);
        return response()->json(['message' => 'Szoba törölve']);
    }
}
