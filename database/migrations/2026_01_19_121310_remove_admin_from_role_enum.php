<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 'admin' ko enum se remove karo, sirf 'patient' aur 'doctor' reh jayen
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor') DEFAULT 'patient'");
        
        // Note: Agar koi user already 'admin' hai, to wo invalid ho jayega – isko handle karne ke liye optional: sabko 'patient' pe set kar do except ek admin ko
        // DB::table('users')->where('role', 'admin')->update(['role' => 'patient']);  // Uncomment agar chahiye
    }

    public function down()
    {
        // Rollback: 'admin' wapis add kar do
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor', 'admin') DEFAULT 'patient'");
    }
};