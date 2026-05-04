<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Deferred FK: users.employee_id → employees.id
// Could not be added in the users migration because employees did not exist yet.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
    }
};
