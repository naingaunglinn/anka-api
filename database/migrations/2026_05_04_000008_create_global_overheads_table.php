<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_overheads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('category', 255);
            $table->text('description')->nullable();
            $table->decimal('monthly_cost', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->index('tenant_id', 'idx_overheads_tenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_overheads');
    }
};
