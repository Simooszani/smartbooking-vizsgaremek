<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
        ]);

        $existing = Review::where('hotel_id', $validated['hotel_id'])
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Ehhez a szállodához már írtál véleményt!'
            ], 422);
        }

        $review = Review::create([
            'hotel_id' => $validated['hotel_id'],
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        $avgRating = Review::where('hotel_id', $validated['hotel_id'])->avg('rating');
        Hotel::where('id', $validated['hotel_id'])->update(['rating' => round($avgRating, 1)]);

        return response()->json($review->load('user'), 201);
    }
}
