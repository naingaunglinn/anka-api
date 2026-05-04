<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 255);
            $table->string('manager', 255)->nullable();
            $table->integer('headcount')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->index('tenant_id', 'idx_departments_tenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
