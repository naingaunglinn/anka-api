<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// The default Sanctum migration uses morphs() which creates tokenable_id as bigint.
// Because users.id is UUID we must change tokenable_id to varchar(36).
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE varchar(36)');
        }
    }

    public function down(): void
    {
        // Irreversible without truncating tokens; leave column as-is on rollback.
    }
};
