<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('created_by');
            $table->enum('report_type', ['daily', 'weekly', 'monthly', 'custom'])->default('daily');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_path')->nullable();
            $table->enum('file_format', ['pdf', 'excel', 'csv'])->default('pdf');
            $table->integer('total_sessions')->default(0);
            $table->integer('total_students')->default(0);
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
