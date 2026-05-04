<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_hard_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('deal_id');
            $table->uuid('employee_id');
            $table->decimal('allocated_hours', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unique(['deal_id', 'employee_id']);
            $table->index('employee_id', 'idx_deal_hard_assign_emp');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE deal_hard_assignments ADD CONSTRAINT check_hard_assign_hours CHECK (allocated_hours >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_hard_assignments');
    }
};
