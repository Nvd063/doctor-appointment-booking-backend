<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DoctorController extends Controller
{
    public function search(Request $request)
    {
        $day = $request->query('day');
        $category = $request->query('category'); // 👈 Frontend se Category lo

        $query = User::where('role', 'doctor');

        // Agar Category select ki gayi hai, to filter lagao
        if ($category && $category !== 'All') {
            $query->where('specialization', $category);
        }

        // Schedule wala filter
        if ($day) {
            $query->whereHas('schedules', function ($q) use ($day) {
                $q->where('day', $day);
            });
        }

        $doctors = $query->with('schedules')->get();

        return response()->json($doctors);
    }
}