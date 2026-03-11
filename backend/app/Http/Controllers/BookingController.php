<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        try {
            // Csak a bejelentkezett user foglalásait kérjük le
            return \App\Models\Booking::with(['room.hotel'])
                ->where('user_id', auth()->id())
                ->get();
        } catch (\Exception $e) {
            // Ha hiba van, küldjük el a hibaüzenetet a válaszban, hogy lásd a konzolban
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        return Booking::create($request->all());
    }

    public function show($id)
    {
        return Booking::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update($request->all());

        return $booking;
    }

    public function destroy($id)
    {
        Booking::destroy($id);
        return response()->json(['message'=>'Törölve']);
    }
}
