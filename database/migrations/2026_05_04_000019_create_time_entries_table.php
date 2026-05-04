<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// approved_by references users(id) as uuid — users.id is kept as uuid per project hard rules.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('project_id');
            $table->uuid('employee_id');
            $table->uuid('approved_by')->nullable();
            $table->string('task', 255);
            $table->date('date');
            $table->decimal('hours', 6, 2);
            $table->boolean('billable')->default(true);
            $table->string('status', 50)->default('Draft');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('restrict');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['project_id', 'date'], 'idx_time_entries_project_date');
            $table->index(['employee_id', 'date'], 'idx_time_entries_employee_date');
            $table->index('status', 'idx_time_entries_status');
            $table->index(['tenant_id', 'date'], 'idx_time_entries_tenant_date');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE time_entries ADD CONSTRAINT check_time_entries_hours CHECK (hours > 0)');
            DB::statement("ALTER TABLE time_entries ADD CONSTRAINT check_time_entries_status CHECK (status IN ('Draft','Pending','Approved','Rejected'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
