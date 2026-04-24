<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('level')->nullable()->after('specialization');
            $table->string('section')->nullable()->after('level');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->integer('level')->nullable()->after('specialization');
            $table->string('section')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['level', 'section']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['level', 'section']);
        });
    }
};
