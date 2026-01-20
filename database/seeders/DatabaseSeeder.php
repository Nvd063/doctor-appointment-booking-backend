<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Doctors ki List (Data Array)
        $doctors = [
            ['name' => 'Dr. Ali',    'email' => 'ali@gmail.com',    'role' => 'doctor'],
            ['name' => 'Dr. Sara',   'email' => 'sara@gmail.com',   'role' => 'doctor'],
            ['name' => 'Dr. Umar',   'email' => 'umar@gmail.com',   'role' => 'doctor'],
            ['name' => 'Dr. Fatima', 'email' => 'fatima@gmail.com', 'role' => 'doctor'],
            ['name' => 'Dr. Zain',   'email' => 'zain@gmail.com',   'role' => 'doctor'],
        ];

        // 2. Loop chala kar Doctors aur Schedules create karna
        foreach ($doctors as $docData) {
            // User Create karo
            $doctor = User::create([
                'name'     => $docData['name'],
                'email'    => $docData['email'],
                'password' => Hash::make('password123'),
                'role'     => $docData['role'],
            ]);

            // Har Doctor ke liye Monday se Saturday ka Schedule banao
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            foreach ($days as $day) {
                Schedule::create([
                    'doctor_id'  => $doctor->id,
                    'day'        => $day,
                    // Har doctor ka thora different time rakhnay k liye random logic (Optional)
                    // Filhal sabka same 09:00 - 17:00 rakhtay hain taake testing asaan ho
                    'start_time' => '09:00',
                    'end_time'   => '17:00',
                ]);
            }
        }

        // 3. Aik Patient bhi bana detay hain testing ke liye
        // User::create([
        //     'name'     => 'Ahmed Patient',
        //     'email'    => 'ahmed@gmail.com',
        //     'password' => Hash::make('password123'),
        //     'role'     => 'patient',
        // ]);

        echo "✅ Success! 5 Doctors aur unke Schedules add ho gaye hain.\n";
    }
}