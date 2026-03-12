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
            'is_admin' => false
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
        return response()->json(Auth::user());
    }

    public function getAllUsers()
    {
        return response()->json(User::all());
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
}