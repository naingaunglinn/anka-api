<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_ghost_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('deal_id');
            $table->string('role_type', 50);
            $table->integer('quantity')->default(1);
            $table->integer('months')->default(1);
            $table->decimal('avg_monthly_salary', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->index('deal_id', 'idx_deal_ghost_roles_deal');
            $table->index(['deal_id', 'role_type'], 'idx_deal_ghost_roles_type');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE deal_ghost_roles ADD CONSTRAINT check_ghost_role_type CHECK (role_type IN ('frontend','backend','pm','qa','design'))");
            DB::statement('ALTER TABLE deal_ghost_roles ADD CONSTRAINT check_ghost_quantity CHECK (quantity >= 1)');
            DB::statement('ALTER TABLE deal_ghost_roles ADD CONSTRAINT check_ghost_months CHECK (months >= 1)');
            DB::statement('ALTER TABLE deal_ghost_roles ADD CONSTRAINT check_ghost_salary CHECK (avg_monthly_salary >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_ghost_roles');
    }
};
