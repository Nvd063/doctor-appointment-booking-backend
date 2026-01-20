<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentBooked;
use App\Models\Appointment;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon; // <--- HERO OF THE PROJECT

class AppointmentController extends Controller
{
    public function bookAppointment(Request $request)
    {
        // 1. Basic Validation
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d', // e.g., 2024-02-20
            'time' => 'required|date_format:H:i',   // e.g., 14:00
        ]);

        // --- CARBON LEARNING PART STARTS HERE ---

        // Step 2: Date ko Carbon object mein convert karein
        // String "2024-02-20" ab Carbon object ban gya
        $requestedDate = Carbon::parse($request->date);
        $requestedTime = Carbon::parse($request->time);

        // Check A: Kya date guzar chuki hai?
        // 'startOfDay()' is liye lagaya taake agar aaj ki date ho to fail na ho
        if ($requestedDate->lt(Carbon::today())) {
            return response()->json(['message' => 'You cannot book an appointment in the past.'], 400);
        }

        // Check B: Kya Doctor us din available hai?
        // $requestedDate->format('l') humein din ka naam dega e.g., "Monday"
        $dayName = $requestedDate->format('l');

        $schedule = Schedule::where('doctor_id', $request->doctor_id)
            ->where('day', $dayName)
            ->first();

        if (!$schedule) {
            return response()->json(['message' => "Doctor is not available on $dayName."], 400);
        }

        // Check C: Kya Time doctor ki timing ke beech mein hai?
        // Database se time string format mein aata hai, isliye Carbon::parse kia
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);

        // 'between()' check karta hai ke time range ke andar hai ya nahi (inclusive)
        if (!$requestedTime->between($startTime, $endTime)) {
            return response()->json([
                'message' => "Doctor is only available between " . $startTime->format('h:i A') . " and " . $endTime->format('h:i A')
            ], 400);
        }

        // --- CARBON PART ENDS ---

        // Check D: Database Check (Conflict)
        // Kya is doctor ke paas, is date par, is time par pehlay se booking hai?
        $exists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->date)
            ->where('appointment_time', $request->time)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'This time slot is already booked.'], 409);
        }

        // 3. Final Booking (Agar sab checks pass ho gaye)
        $appointment = Appointment::create([
            'patient_id' => $request->user()->id, // Token se user ID liya
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->date,
            'appointment_time' => $request->time,
            'status' => 'pending'
        ]);


        return response()->json([
            'message' => 'Appointment booked successfully!',
            'appointment' => $appointment,
            // Bonus: User ko response mein readable date dikhana
            'formatted_date' => $requestedDate->format('l, jS F Y') // e.g. Monday, 20th January 2026
        ], 201);
    }

    // Doctor ko uski sari appointments dikhao
    public function getDoctorAppointments(Request $request)
    {
        // 'with()' ka matlab hai Eager Loading. 
        // Yani appointment ke sath sath 'patient' ka data bhi le aao.
        $appointments = Appointment::where('doctor_id', $request->user()->id)
            ->with('patient:id,name,email') // Sirf zaroori columns layen
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        return response()->json($appointments);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        // Appointment dhondo
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        // SECURITY CHECK: 
        // Kya yeh appointment isi doctor ki hai? 
        // Aisa na ho Doctor A, Doctor B ki appointment cancel kar de.
        if ($appointment->doctor_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $appointment->status = $request->status;
        $appointment->save();

        return response()->json([
            'message' => 'Appointment status updated to ' . $request->status,
            'appointment' => $appointment
        ]);
    }


    // Patient ko uski apni appointments dikhao
    public function getPatientAppointments(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Upcoming Appointments (Aaj ya aanay walay kal ki)
        $upcoming = Appointment::where('patient_id', $userId)
            ->where('appointment_date', '>=', Carbon::today()->toDateString()) // Carbon Magic
            ->where('status', '!=', 'cancelled') // Cancelled wali "upcoming" mein na dikhayen
            ->with('doctor:id,name') // Doctor ka naam bhi chahiye
            ->orderBy('appointment_date', 'asc')
            ->get();

        // 2. Past History (Jo guzar gayin)
        $history = Appointment::where('patient_id', $userId)
            ->where(function ($query) {
                // Ya toh date guzar gayi ho...
                $query->where('appointment_date', '<', Carbon::today()->toDateString())
                    // ...ya phir status 'cancelled' ya 'completed' ho
                    ->orWhereIn('status', ['cancelled', 'completed']);
            })
            ->with('doctor:id,name')
            ->orderBy('appointment_date', 'desc') // Recent wali pehlay
            ->get();

        return response()->json([
            'upcoming_appointments' => $upcoming,
            'past_history' => $history
        ]);
    }

    // AppointmentController.php k andar

    public function myAppointments(Request $request)
    {
        // 1. Logged in Doctor ki ID nikalo
        $doctor = $request->user();

        // 2. Sirf is doctor ki appointments lao, sath ma Patient ka naam bhi
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->with('patient') // Patient ka data (naam, email) bhi lao
            ->orderBy('date', 'asc') // Puraani pehlay, nayi baad ma
            ->get();

        return response()->json($appointments);
    }

    // Dashboard Stats k liye
    public function dashboardStats(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'doctor') {
            // Doctor k liye stats
            $total = Appointment::where('doctor_id', $user->id)->count();
            $pending = Appointment::where('doctor_id', $user->id)->where('status', 'pending')->count();
            $completed = Appointment::where('doctor_id', $user->id)->where('status', 'completed')->count();

            return response()->json([
                'total' => $total,
                'pending' => $pending,
                'completed' => $completed
            ]);
        } else {
            // Patient k liye stats
            $total = Appointment::where('patient_id', $user->id)->count();
            $upcoming = Appointment::where('patient_id', $user->id)
                ->where('status', 'pending')
                ->count();

            return response()->json([
                'total' => $total,
                'upcoming' => $upcoming
            ]);
        }
    }

    // Patient apni appointment cancel kare
    public function cancelByPatient(Request $request, $id)
    {
        $user = $request->user();

        // Appointment dhoondo jo is patient ki ho
        $appointment = Appointment::where('id', $id)
                                  ->where('patient_id', $user->id)
                                  ->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found or unauthorized'], 403);
        }

        // Check: Sirf 'pending' appointment cancel ho sakti hai
        if ($appointment->status !== 'pending') {
            return response()->json(['message' => 'Cannot cancel processed appointment'], 400);
        }

        $appointment->status = 'cancelled';
        $appointment->save();

        return response()->json(['message' => 'Appointment cancelled successfully']);
    }

}