<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('deal_id')->nullable();
            $table->string('contract_number', 255)->unique();
            $table->string('client', 255);
            $table->decimal('total_value', 14, 2)->default(0);
            $table->decimal('revenue_recognized', 14, 2)->default(0);
            $table->string('status', 50)->default('Draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('set null');
            $table->index(['tenant_id', 'status'], 'idx_contracts_tenant_status');
            $table->index('deal_id', 'idx_contracts_deal');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            // Sequence-based default: CON-0001, CON-0002, …
            DB::statement("ALTER TABLE contracts ALTER COLUMN contract_number SET DEFAULT ('CON-' || lpad(nextval('contract_number_seq')::text, 4, '0'))");
            DB::statement("ALTER TABLE contracts ADD CONSTRAINT check_contracts_status CHECK (status IN ('Draft','Active','Completed','Cancelled'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
