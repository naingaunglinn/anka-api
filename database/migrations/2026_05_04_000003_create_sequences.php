<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE SEQUENCE IF NOT EXISTS contract_number_seq START 1');
            DB::statement('CREATE SEQUENCE IF NOT EXISTS invoice_number_seq  START 1042');
            DB::statement('CREATE SEQUENCE IF NOT EXISTS project_number_seq  START 101');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP SEQUENCE IF EXISTS project_number_seq');
            DB::statement('DROP SEQUENCE IF EXISTS invoice_number_seq');
            DB::statement('DROP SEQUENCE IF EXISTS contract_number_seq');
        }
    }
};
