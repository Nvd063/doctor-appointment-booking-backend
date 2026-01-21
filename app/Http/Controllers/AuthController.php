<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. REGISTER (Naya User)
    // app/Http/Controllers/AuthController.php

public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:6'
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'patient' // 👈 FORCED: Public user hamesha Patient banega
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'role' => 'patient'
    ], 201);
}

    // 2. LOGIN (Purana User)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided.'],
            ]);
        }

        // Token de do
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'role' => $user->role, // Frontend ko batana ke yeh doctor hai ya patient
            'token' => $token
        ]);
    }

    // 3. LOGOUT
    public function logout(Request $request)
    {
        // Token delete kar do (Revoke)
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getSpecializations()
    {
        // 1. Sirf Doctors ki specializations uthao
        // 2. Jo Khali (Null) na hon
        // 3. Duplicate na hon (distinct)
        $specs = User::where('role', 'doctor')
            ->whereNotNull('specialization')
            ->where('specialization', '!=', '') // Empty string bhi na ho
            ->distinct()
            ->pluck('specialization'); // Sirf naam chahiye, poora user nahi

        return response()->json($specs);
    }
}