<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (Kon book kar raha hai aur kis se?)
            // constrained() ka matlab hai agar user delete hua to appointment bhi urr jaye gi (cascade)
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');

            // Core Logic Columns
            $table->date('appointment_date'); // Sirf Date (e.g., 2024-02-20)
            $table->time('appointment_time'); // Sirf Time (e.g., 14:00:00)

            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
