<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function setSchedule(Request $request)
    {
        // 1. Validation
        $request->validate([
            'day'        => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time', // 'after' check ensure karta hai end time bara ho
        ]);

        // 2. Save or Update Logic
        // Hum check karen gay ke is Doctor ka is Day ka schedule pehlay se hai?
        $schedule = Schedule::updateOrCreate(
            [
                'doctor_id' => $request->user()->id, // Token se Doctor ki ID
                'day'       => $request->day,        // Din (e.g., Monday)
            ],
            [
                'start_time' => $request->start_time,
                'end_time'   => $request->end_time,
            ]
        );

        return response()->json([
            'message' => 'Schedule updated successfully!',
            'schedule' => $schedule
        ], 200);
    }
}