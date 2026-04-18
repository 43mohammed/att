<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('instructor_id');
            $table->dateTime('session_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('qr_code_token')->unique();
            $table->string('qr_code_image')->nullable();
            $table->boolean('gps_required')->default(false);
            $table->boolean('nfc_active')->default(false);
            $table->decimal('classroom_latitude', 10, 8)->nullable();
            $table->decimal('classroom_longitude', 11, 8)->nullable();
            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
