<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;

class AdminController extends Controller
{
    // 1. Saray Users ki List lao
    public function getAllUsers(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $doctors = User::where('role', 'doctor')->get();
        $patients = User::where('role', 'patient')->get();

        return response()->json([
            'doctors' => $doctors,
            'patients' => $patients
        ]);
    }

    // 2. User Delete karo
    public function deleteUser(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // 3. Add Doctor (Admin Only)
    public function addDoctor(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'specialization' => 'required|string'
        ]);

        $doctor = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'doctor',
            'specialization' => $request->specialization
        ]);

        return response()->json(['message' => 'Doctor added successfully!', 'doctor' => $doctor]);
    }

    // 4. Get User Details (View History & Schedule) - FIXED & MERGED
    public function getUserDetails($id)
    {
        // 👇 Step 1: User dhoondo aur sath ma 'schedules' bhi lao (Eager Loading)
        $user = User::with('schedules')->findOrFail($id);

        $appointments = [];

        // 👇 Step 2: Role k hisaab se appointments lao
        if ($user->role === 'doctor') {
            // Agar Doctor hai to wo appointments lao jahan ye doctor hai
            $appointments = Appointment::where('doctor_id', $id)
                            ->with('patient') // Patient ka naam dikhanay k liye
                            ->orderBy('id', 'desc')
                            ->get();
        } else {
            // Agar Patient hai to wo appointments lao jahan ye patient hai
            $appointments = Appointment::where('patient_id', $id)
                            ->with('doctor') // Doctor ka naam dikhanay k liye
                            ->orderBy('id', 'desc')
                            ->get();
        }

        // 👇 Step 3: Sab kuch wapis bhejo
        return response()->json([
            'user' => $user, // Iske andar ab 'schedules' bhi honge
            'appointments' => $appointments
        ]);
    }
}