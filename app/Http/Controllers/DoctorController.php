<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function search(Request $request)
    {
        // Query Builder Start
        // Hum nay kaha: "Mujhay sirf Doctors chahiye"
        $query = User::where('role', 'doctor');

        // Filter 1: By Name (Agar user nay 'name' bheja hai to filter lagao)
        $query->when($request->name, function ($q) use ($request) {
            // 'like' search se milta julta naam dhoondy ga
            return $q->where('name', 'like', '%' . $request->name . '%');
        });

        // Filter 2: By Day (Agar user nay 'day' bheja hai e.g., Monday)
        // Yahan hum RELATIONSHIP ke andar ja kar search karen gay
        $query->when($request->day, function ($q) use ($request) {
            return $q->whereHas('schedules', function ($subQuery) use ($request) {
                $subQuery->where('day', $request->day);
            });
        });

        // Results lao aur sath mein unka schedule bhi dikhao
        $doctors = $query->with('schedules')->get();

        return response()->json([
            'count' => $doctors->count(),
            'doctors' => $doctors
        ]);
    }
}