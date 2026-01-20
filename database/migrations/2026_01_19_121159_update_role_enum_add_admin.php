<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Existing enum ko alter kar ke 'admin' add karo
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor', 'admin') DEFAULT 'patient'");
        
        // Ya agar table name different hai (jaise 'users' nahi), to change kar lo
    }

    public function down()
    {
        // Rollback: 'admin' remove kar do (original state pe wapis)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor') DEFAULT 'patient'");
    }
};