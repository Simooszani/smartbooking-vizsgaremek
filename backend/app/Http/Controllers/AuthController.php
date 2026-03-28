<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => 'user'
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Hibás e-mail cím vagy jelszó.'
            ], 401);
        }

        // Check suspension
        if ($user->isSuspended()) {
            return response()->json([
                'message' => 'A fiókod fel van függesztve!',
                'suspended_until' => $user->suspended_until->toISOString(),
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json(['message' => 'Kijelentkezve']);
    }

    public function me()
    {
        $user = Auth::user();
        $data = $user->toArray();

        if ($user->isHotelAdmin() && $user->managed_hotel_id) {
            $data['managed_hotel'] = $user->managedHotel;
        }

        return response()->json($data);
    }

    public function getAllUsers()
    {
        return response()->json(User::with('managedHotel')->get());
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Felhasználó nem található'], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Saját magadat nem törölheted!'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Felhasználó sikeresen törölve']);
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:user,hotel_admin,admin,super_admin',
            'managed_hotel_id' => 'nullable|exists:hotels,id',
        ]);

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);
        $newRole = $request->role;

        // Csak super_admin adhat admin vagy super_admin jogot
        if (in_array($newRole, ['admin', 'super_admin']) && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'message' => 'Csak a főadmin adhat admin jogosultságot!'
            ], 403);
        }

        // Admin csak hotel_admin-t és user-t adhat
        if ($currentUser->role === 'admin' && !in_array($newRole, ['user', 'hotel_admin'])) {
            return response()->json([
                'message' => 'Nincs jogosultságod ehhez a szerepkörhöz!'
            ], 403);
        }

        $targetUser->role = $newRole;
        $targetUser->managed_hotel_id = ($newRole === 'hotel_admin') ? $request->managed_hotel_id : null;
        $targetUser->save();

        return response()->json($targetUser->load('managedHotel'));
    }
}
