<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class DoctorController extends Controller
{
    public function search(Request $request)
    {
        // 1. Basic Query
        $query = User::where('role', 'doctor')->with('schedules');

        // 2. Filters
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('category') && $request->category != 'All') {
            $query->where('specialization', $request->category);
        }

        if ($request->filled('day')) {
            $day = $request->day;
            $query->whereHas('schedules', function($q) use ($day) {
                $q->where('day', $day);
            });
        }

        // 👇 3. BOOKED SLOTS LOGIC (New)
        // Agar Frontend ne 'date' bheji hai, to us date ki booked appointments bhi lao
        if ($request->filled('date')) {
            $targetDate = $request->date;
            
            $query->with(['appointments' => function($q) use ($targetDate) {
                $q->where('appointment_date', $targetDate)
                  ->where('status', '!=', 'completed') // Sirf wo jo abhi complete nahi hui
                  ->select('doctor_id', 'appointment_time'); // Sirf time chahiye
            }]);
        }

        $doctors = $query->get();
        return response()->json($doctors);
    }
}