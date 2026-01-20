<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

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
    Route::get('/doctor/appointments', [AppointmentController::class, 'myAppointments']);

    Route::get('/patient/appointments', [AppointmentController::class, 'getPatientAppointments']);
    Route::get('/dashboard-stats', [AppointmentController::class, 'dashboardStats']);
    Route::put('/patient/appointments/{id}/cancel', [AppointmentController::class, 'cancelByPatient']);

    Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

    

    //profile controller
    Route::get('/profile', [ProfileController::class, 'getUser']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);

    // --- DOCTOR GROUP (Only Doctors) ---
    Route::middleware('role:doctor')->group(function () {
        // Schedule Management
        Route::post('/set-schedule', [ScheduleController::class, 'setSchedule']);

        // Appointment Management
        Route::get('/doctor/appointments', [AppointmentController::class, 'getDoctorAppointments']);
        Route::put('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    });
    Route::get('/admin/users/{id}', [AdminController::class, 'getUserDetails']);

    Route::get('/specializations', [AuthController::class, 'getSpecializations']);
    // --- PATIENT GROUP (Only Patients) ---
    Route::middleware('role:patient')->group(function () {
        // Search Doctors
        Route::get('/doctors/search', [DoctorController::class, 'search']);

        // Appointment Operations
        Route::post('/book-appointment', [AppointmentController::class, 'bookAppointment']);
        Route::get('/patient/my-appointments', [AppointmentController::class, 'getPatientAppointments']);
    });

});