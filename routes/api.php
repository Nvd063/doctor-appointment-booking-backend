<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DoctorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================
// 1. PUBLIC ROUTES (No Token Needed)
// ==========================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ==========================
// 2. PROTECTED ROUTES (Token Required)
// ==========================
Route::middleware('auth:sanctum')->group(function () {

    // --- Common Routes (Doctor & Patient Dono ke liye) ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    // --- DOCTOR GROUP (Only Doctors) ---
    Route::middleware('role:doctor')->group(function () {
        // Schedule Management
        Route::post('/set-schedule', [ScheduleController::class, 'setSchedule']);
        
        // Appointment Management
        Route::get('/doctor/appointments', [AppointmentController::class, 'getDoctorAppointments']);
        Route::put('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    });


    // --- PATIENT GROUP (Only Patients) ---
    Route::middleware('role:patient')->group(function () {
        // Search Doctors
        Route::get('/doctors/search', [DoctorController::class, 'search']);

        // Appointment Operations
        Route::post('/book-appointment', [AppointmentController::class, 'bookAppointment']);
        Route::get('/patient/my-appointments', [AppointmentController::class, 'getPatientAppointments']);
    });

});