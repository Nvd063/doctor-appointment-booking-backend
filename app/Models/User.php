<?php

namespace App\Models;

// ... baki imports wese hi rehne den
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Role add karna na bhoolen
        'specialization',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // --- Relationships ---

    // Doctor ke liye: Usnay kitni appointments receive ki hain
    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    // Patient ke liye: Usnay kitni appointments book ki hain
    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    // Doctor ke liye: Uska schedule kya hai
    public function schedules()
    {
        // Doctor ke bohot saray schedules ho saktay hain
        return $this->hasMany(Schedule::class, 'doctor_id');
    }
}