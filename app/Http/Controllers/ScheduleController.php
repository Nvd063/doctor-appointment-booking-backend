<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    // 1. ADD NEW SLOT
    public function store(Request $request)
    {
        $request->validate([
            'day' => 'required',
            'start_time' => 'required',
            'end_time' => 'required'
        ]);

        $schedule = Schedule::create([
            'doctor_id' => Auth::id(),
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'Slot added', 'data' => $schedule]);
    }

    // 2. UPDATE SLOT (Ye missing hoga)
    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        // Check ownership
        if ($schedule->doctor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schedule->update([
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json(['message' => 'Updated successfully']);
    }

    // 3. DELETE SLOT (Ye bhi missing hoga)
    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        if ($schedule->doctor_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schedule->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}