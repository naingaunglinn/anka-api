<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name', 255);
            $table->string('client', 255)->nullable();
            $table->decimal('estimated_value', 14, 2)->nullable();
            $table->smallInteger('win_probability')->default(0);
            $table->string('status', 50)->default('inquiry');
            $table->decimal('client_budget', 14, 2)->nullable();
            $table->integer('timeline_months')->nullable();
            $table->decimal('workload_hours', 10, 2)->nullable();
            $table->text('workload_description')->nullable();
            $table->decimal('target_margin', 5, 2)->nullable();
            $table->decimal('base_labor_cost', 14, 2)->nullable();
            $table->decimal('overhead_cost', 14, 2)->nullable();
            $table->decimal('buffer_cost', 14, 2)->nullable();
            $table->decimal('total_estimated_cost', 14, 2)->nullable();
            $table->decimal('estimated_gross_profit', 14, 2)->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->index(['tenant_id', 'status'], 'idx_deals_tenant_status');
            $table->index(['tenant_id', 'status', 'updated_at'], 'idx_deals_tenant_status_upd');
            $table->index(['tenant_id', 'client'], 'idx_deals_tenant_client');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE deals ADD CONSTRAINT check_deals_status CHECK (status IN ('lead','inquiry','opportunity','proposal','contract','won','lost'))");
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_win_probability CHECK (win_probability BETWEEN 0 AND 100)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_estimated_value CHECK (estimated_value >= 0)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_client_budget CHECK (client_budget >= 0)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_workload_hours CHECK (workload_hours >= 0)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_base_labor_cost CHECK (base_labor_cost >= 0)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_overhead_cost CHECK (overhead_cost >= 0)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_buffer_cost CHECK (buffer_cost >= 0)');
            DB::statement('ALTER TABLE deals ADD CONSTRAINT check_deals_total_estimated_cost CHECK (total_estimated_cost >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
