<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('department');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('specialization');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('specialization');
        });
    }
};
