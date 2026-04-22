<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // List conversations for current user (guest sees their hotel chats, hotel_admin sees their hotel's chats)
    public function conversations()
    {
        $user = Auth::user();

        if ($user->isHotelAdmin() && $user->managed_hotel_id) {
            // Hotel admin sees all conversations for their hotel
            $conversations = Message::where('hotel_id', $user->managed_hotel_id)
                ->select('hotel_id', 'user_id')
                ->with(['user:id,name,email', 'hotel:id,name'])
                ->selectRaw('MAX(created_at) as last_message_at')
                ->selectRaw('SUM(CASE WHEN is_read = 0 AND sender_id != ? THEN 1 ELSE 0 END) as unread_count', [$user->id])
                ->groupBy('hotel_id', 'user_id')
                ->orderByDesc('last_message_at')
                ->get();
        } else {
            // Regular user sees their conversations
            $conversations = Message::where('user_id', $user->id)
                ->select('hotel_id', 'user_id')
                ->with(['hotel:id,name'])
                ->selectRaw('MAX(created_at) as last_message_at')
                ->selectRaw('SUM(CASE WHEN is_read = 0 AND sender_id != ? THEN 1 ELSE 0 END) as unread_count', [$user->id])
                ->groupBy('hotel_id', 'user_id')
                ->orderByDesc('last_message_at')
                ->get();
        }

        return response()->json($conversations);
    }

    // Get messages for a specific conversation
    public function messages(Request $request, $hotelId, $userId)
    {
        $user = Auth::user();

        // Authorization: only the guest themselves or the hotel admin can view
        if ($user->id != $userId && !($user->isHotelAdmin() && $user->managed_hotel_id == $hotelId) && !$user->isAdmin()) {
            return response()->json(['message' => 'Nincs jogosultságod!'], 403);
        }

        $messages = Message::where('hotel_id', $hotelId)
            ->where('user_id', $userId)
            ->with('sender:id,name,role')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read (those not sent by current user)
        Message::where('hotel_id', $hotelId)
            ->where('user_id', $userId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    // Send a message
    public function send(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'message' => 'required|string|max:2000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $user = Auth::user();
        $hotelId = $request->hotel_id;

        // Determine the guest user_id
        if ($user->isHotelAdmin() && $user->managed_hotel_id == $hotelId) {
            // Hotel admin replying to a guest
            $guestId = $request->user_id;
            if (!$guestId) {
                return response()->json(['message' => 'user_id szükséges!'], 422);
            }
        } else {
            // Guest sending to hotel
            $guestId = $user->id;
        }

        $message = Message::create([
            'hotel_id' => $hotelId,
            'user_id' => $guestId,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        $message->load('sender:id,name');

        return response()->json($message, 201);
    }

    // Unread message count for navbar badge
    public function unreadCount()
    {
        $user = Auth::user();

        if ($user->isHotelAdmin() && $user->managed_hotel_id) {
            $count = Message::where('hotel_id', $user->managed_hotel_id)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        } else {
            $count = Message::where('user_id', $user->id)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json(['count' => $count]);
    }
}
