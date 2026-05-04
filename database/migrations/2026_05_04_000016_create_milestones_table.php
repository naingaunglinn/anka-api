<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('contract_id');
            $table->string('name', 255);
            $table->date('due_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 50)->default('Pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->index('contract_id', 'idx_milestones_contract');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE milestones ADD CONSTRAINT check_milestones_amount CHECK (amount >= 0)');
            DB::statement("ALTER TABLE milestones ADD CONSTRAINT check_milestones_status CHECK (status IN ('Pending','In Progress','Completed'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
