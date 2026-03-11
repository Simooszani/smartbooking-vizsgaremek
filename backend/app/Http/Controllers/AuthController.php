<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validáció
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Felhasználó létrehozása
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Token generálás (Sanctum esetén)
        $token = $user->createToken('auth_token')->plainTextToken;

        // --- EZ A KRITIKUS RÉSZ: SEMMI REDIRECT! ---
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201); 
    }

    public function login(Request $request)
    {
        // 1. Validáljuk a bejövő adatokat
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Megpróbáljuk a beléptetést
        // Az Auth::attempt automatikusan ellenőrzi a Hash-elt jelszót!
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Hibás email cím vagy jelszó.'
            ], 401);
        }

        // 3. Ha sikerült, generálunk tokent
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sikeres kijelentkezés']);
    }
}