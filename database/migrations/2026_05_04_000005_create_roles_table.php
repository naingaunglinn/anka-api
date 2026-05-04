<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('department_id')->nullable();
            $table->string('title', 255);
            $table->string('department', 255)->nullable();
            $table->decimal('rate', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->index(['tenant_id', 'department_id'], 'idx_roles_tenant_dept');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
