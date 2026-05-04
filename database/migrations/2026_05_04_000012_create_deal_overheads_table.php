<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_overheads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('deal_id');
            $table->string('name', 255);
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->index('deal_id', 'idx_deal_overheads_deal');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE deal_overheads ADD CONSTRAINT check_deal_overheads_cost CHECK (cost >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_overheads');
    }
};
