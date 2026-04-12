<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // List conversations for current user
    public function conversations()
    {
        $user = Auth::user();

        $query = Message::query();

        if ($user->isSuperAdmin()) {
            // super_admin sees ALL conversations
        } elseif ($user->isAdmin() && $user->admin_city) {
            // Területi admin: csak az adott városban lévő hotelek chatjeit látja
            $cityHotelIds = Hotel::where('address', 'like', '%' . $user->admin_city . '%')->pluck('id');
            $query->whereIn('hotel_id', $cityHotelIds);
        } elseif ($user->isAdmin()) {
            // Admin város nélkül: mindent lát
        } elseif ($user->isHotelAdmin() && $user->managed_hotel_id) {
            $query->where('hotel_id', $user->managed_hotel_id);
        } else {
            $query->where('user_id', $user->id);
        }

        $rows = $query
            ->selectRaw('hotel_id, user_id, MAX(created_at) as last_message_at, SUM(CASE WHEN is_read = 0 AND sender_id != ? THEN 1 ELSE 0 END) as unread_count', [$user->id])
            ->groupBy('hotel_id', 'user_id')
            ->orderByDesc('last_message_at')
            ->get();

        $userIds = $rows->pluck('user_id')->unique()->values();
        $hotelIds = $rows->pluck('hotel_id')->unique()->values();
        $users = \App\Models\User::whereIn('id', $userIds)->get(['id', 'name', 'email'])->keyBy('id');
        $hotels = Hotel::whereIn('id', $hotelIds)->get(['id', 'name'])->keyBy('id');

        $conversations = $rows->map(function ($row) use ($users, $hotels) {
            return [
                'hotel_id' => (int) $row->hotel_id,
                'user_id' => (int) $row->user_id,
                'last_message_at' => $row->last_message_at,
                'unread_count' => (int) $row->unread_count,
                'user' => $users->get($row->user_id),
                'hotel' => $hotels->get($row->hotel_id),
            ];
        });

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
            ->with('sender:id,name')
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

        $isStaff = $user->isAdmin() || ($user->isHotelAdmin() && $user->managed_hotel_id == $hotelId);

        if ($isStaff) {
            $guestId = $request->user_id;
            if (!$guestId) {
                return response()->json(['message' => 'user_id szükséges!'], 422);
            }
        } else {
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

    // Delete a single message (own message, or super_admin can delete any)
    public function destroyMessage($id)
    {
        $user = Auth::user();
        $message = Message::find($id);

        if (!$message) {
            return response()->json(['message' => 'Üzenet nem található!'], 404);
        }

        if ($message->sender_id != $user->id && !$user->isSuperAdmin()) {
            return response()->json(['message' => 'Nincs jogosultságod!'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Üzenet törölve.']);
    }

    // Delete an entire conversation (super_admin only)
    public function destroyConversation($hotelId, $userId)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            return response()->json(['message' => 'Nincs jogosultságod!'], 403);
        }

        $deleted = Message::where('hotel_id', $hotelId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['message' => 'Beszélgetés törölve.', 'deleted' => $deleted]);
    }

    // Unread message count for navbar badge
    public function unreadCount()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $count = 0;
        } elseif ($user->isHotelAdmin() && $user->managed_hotel_id) {
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
