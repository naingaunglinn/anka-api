<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimation_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('deal_id');
            $table->uuid('job_role_id')->nullable();
            $table->text('role_id')->nullable();
            $table->string('feature_name', 255);
            $table->decimal('hours', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('job_role_id')->references('id')->on('roles')->onDelete('set null');
            $table->index('deal_id', 'idx_estimation_deal');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE estimation_resources ADD CONSTRAINT check_estimation_hours CHECK (hours >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('estimation_resources');
    }
};
