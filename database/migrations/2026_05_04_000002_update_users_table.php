<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: add new columns (nullable until data is migrated)
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('employee_id')->nullable()->after('tenant_id');
            $table->string('first_name', 255)->nullable()->after('employee_id');
            $table->string('last_name', 255)->nullable()->after('first_name');
            $table->string('system_role', 50)->default('member')->after('app_role');
            $table->softDeletes();
        });

        // Step 2: populate first_name/last_name from existing name column (pgsql only)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE users
                SET first_name = SPLIT_PART(name, ' ', 1),
                    last_name  = CASE
                                    WHEN POSITION(' ' IN name) > 0
                                    THEN TRIM(SUBSTRING(name FROM POSITION(' ' IN name) + 1))
                                    ELSE ''
                                 END
            ");
            DB::statement('ALTER TABLE users ALTER COLUMN first_name SET NOT NULL');
            DB::statement('ALTER TABLE users ALTER COLUMN last_name  SET NOT NULL');
        }

        // Step 3: drop legacy single-name column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // Step 4: fix tenant_id FK from CASCADE → RESTRICT
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
        });

        // Step 5: add app_role default + CHECK constraint + composite index (pgsql)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN app_role SET DEFAULT 'Sales'");
            DB::statement("ALTER TABLE users ADD CONSTRAINT check_users_app_role CHECK (app_role IN ('Admin','Executive','Sales','Delivery','HR'))");
            DB::statement('CREATE INDEX IF NOT EXISTS idx_users_tenant_email ON users(tenant_id, email)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_users_tenant_email');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS check_users_app_role');
            DB::statement('ALTER TABLE users ALTER COLUMN app_role DROP DEFAULT');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->after('last_name');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("UPDATE users SET name = TRIM(COALESCE(first_name,'') || ' ' || COALESCE(last_name,''))");
            DB::statement('ALTER TABLE users ALTER COLUMN name SET NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['employee_id', 'first_name', 'last_name', 'system_role']);
        });
    }
};
