<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('contract_id')->unique();
            $table->string('project_number', 255)->unique();
            $table->string('name', 255);
            $table->string('client', 255);
            $table->decimal('budget_hours', 10, 2)->default(0);
            $table->decimal('consumed_hours', 10, 2)->default(0);
            $table->string('status', 50)->default('Not Started');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('restrict');
            $table->index(['tenant_id', 'status'], 'idx_projects_tenant_status');
            $table->index('contract_id', 'idx_projects_contract');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            // Sequence-based default: PRJ-101, PRJ-102, …
            DB::statement("ALTER TABLE projects ALTER COLUMN project_number SET DEFAULT ('PRJ-' || lpad(nextval('project_number_seq')::text, 3, '0'))");
            DB::statement('ALTER TABLE projects ADD CONSTRAINT check_projects_budget_hours CHECK (budget_hours >= 0)');
            DB::statement('ALTER TABLE projects ADD CONSTRAINT check_projects_consumed_hours CHECK (consumed_hours >= 0)');
            DB::statement("ALTER TABLE projects ADD CONSTRAINT check_projects_status CHECK (status IN ('Not Started','On Track','At Risk','Over Budget','Completed'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
