<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// id is text DEFAULT 'singleton' — intentional exception to the UUID PK rule.
// For multi-tenant production: id = tenant_id::text, one row per tenant.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->string('id', 255)->default('singleton')->primary();
            $table->uuid('tenant_id')->unique();
            $table->decimal('overhead_percentage', 5, 2)->default(20);
            $table->decimal('buffer_percentage', 5, 2)->default(10);
            $table->decimal('yearly_fixed_cost', 14, 2)->default(0);
            $table->decimal('employer_tax_percentage', 5, 2)->default(8);
            $table->decimal('benefits_percentage', 5, 2)->default(12);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
