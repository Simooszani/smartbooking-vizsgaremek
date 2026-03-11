<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Booking;
use Illuminate\Http\Request;

class HotelController extends Controller
{
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
}