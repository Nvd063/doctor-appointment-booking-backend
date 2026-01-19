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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            // Kis doctor ka schedule hai?
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');

            // Din kaunsa hai? (e.g., "Monday", "Tuesday")
            $table->string('day');

            // Start aur End time (e.g., 09:00:00 to 17:00:00)
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
