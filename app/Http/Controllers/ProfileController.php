<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Schedule;

class ProfileController extends Controller
{
    // 👇 1. GET PROFILE (User + Schedule)
    // Maine dono functions ko merge kar diya hai. Ab yehi function chalay ga.
    public function getUser(Request $request)
    {
        // 1. User nikalo
        $user = $request->user();

        // 2. Schedule nikalo (Direct Query)
        $schedules = Schedule::where('doctor_id', $user->id)->get();

        // 3. User data k sath Schedules mix kr k bhejo
        // $user->toArray() user ka sara data array bana deta hai, phir hum usme schedules add kr dete hain
        $userData = $user->toArray();
        $userData['schedules'] = $schedules;

        return response()->json($userData);
    }

    // 👇 2. UPDATE PROFILE (Ye waisa hi rahe ga)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6',
        ]);

        $user->name = $request->name;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully!', 'user' => $user]);
    }
}