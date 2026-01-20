<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
    ];

    // Appointment kis patient ki hai?
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Appointment kis doctor ke sath hai?
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    
}