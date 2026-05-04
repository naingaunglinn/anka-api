<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug', 255)->unique()->after('name');
            $table->string('plan', 50)->default('free')->after('slug');
            $table->boolean('is_active')->default(true)->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'plan', 'is_active']);
        });
    }
};
