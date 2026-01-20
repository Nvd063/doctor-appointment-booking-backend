<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // 1. User ka data bhejo (Edit form mien dikhanay k liye)
    public function getUser(Request $request) {
        return response()->json($request->user());
    }

    // 2. Profile Update karo
    public function updateProfile(Request $request) {
        $user = $request->user();

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6', // Password optional hai
        ]);

        // Name update
        $user->name = $request->name;

        // Agar password diya hai to update karo, warna wahi rehne do
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user]);
    }
}