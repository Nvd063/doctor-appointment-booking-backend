<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    // 1. Saray Users ki List lao (Doctor aur Patient alag alag)
    public function getAllUsers(Request $request)
    {
        // Check: Sirf Admin hi ye kam kar sakay
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

    // 2. Kisi User ko Delete karo
    public function deleteUser(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $user = User::find($id);
        if ($user) {
            $user->delete(); // User khatam, tata, bye bye!
            return response()->json(['message' => 'User deleted successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // Get Single User Details (Doctor or Patient)
    public function getUserDetails(Request $request, $id)
    {
        // Security Check
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access Denied'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Initialize response data
        $data = [
            'user' => $user,
            'appointments' => [],
            'schedules' => []
        ];

        // 1. If user is a Doctor
        if ($user->role === 'doctor') {
            // Load their schedules
            $data['schedules'] = $user->schedules;

            // Load appointments where this user is the DOCTOR
            // Include patient details
            $data['appointments'] = \App\Models\Appointment::where('doctor_id', $user->id)
                ->with('patient:id,name,email')
                ->orderBy('appointment_date', 'desc') // 👈 Yahan 'date' ki jagah sahi naam likhen (e.g. 'created_at' ya 'appointment_date')
                ->get();
        }

        // 2. If user is a Patient
        elseif ($user->role === 'patient') {
            // Load appointments where this user is the PATIENT
            // Include doctor details
            $data['appointments'] = \App\Models\Appointment::where('patient_id', $user->id)
                ->with('doctor:id,name,specialization')
                ->orderBy('appointment_date', 'desc') // 👈 Yahan bhi change karein
                ->get();
        }

        return response()->json($data);
    }

    
}