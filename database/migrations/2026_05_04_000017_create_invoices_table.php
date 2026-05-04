<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// invoices.total is a GENERATED column (amount + tax) — never set it in PHP.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('contract_id');
            $table->uuid('milestone_id')->nullable();
            $table->string('invoice_number', 255)->unique();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            // total is GENERATED in PostgreSQL — added below via raw SQL
            $table->string('status', 50)->default('Draft');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('restrict');
            $table->foreign('milestone_id')->references('id')->on('milestones')->onDelete('set null');
            $table->index(['tenant_id', 'status'], 'idx_invoices_tenant_status');
            $table->index(['tenant_id', 'status', 'issue_date'], 'idx_invoices_tenant_status_dt');
            $table->index('contract_id', 'idx_invoices_contract');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            // Sequence-based default: INV-1042, INV-1043, …
            DB::statement("ALTER TABLE invoices ALTER COLUMN invoice_number SET DEFAULT ('INV-' || lpad(nextval('invoice_number_seq')::text, 4, '0'))");
            // GENERATED total column: amount + tax, never written by application
            DB::statement('ALTER TABLE invoices ADD COLUMN total numeric(12,2) GENERATED ALWAYS AS (amount + tax) STORED');
            DB::statement('ALTER TABLE invoices ADD CONSTRAINT check_invoices_amount CHECK (amount >= 0)');
            DB::statement('ALTER TABLE invoices ADD CONSTRAINT check_invoices_tax CHECK (tax >= 0)');
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT check_invoices_status CHECK (status IN ('Draft','Pending','Paid','Overdue','Cancelled'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
