<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Str::uuid()->toString();

        DB::table('tenants')->insert([
            'id'         => $tenantId,
            'name'       => 'ANKA Agency',
            'slug'       => 'anka-agency',
            'plan'       => 'pro',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'tenant_id'  => $tenantId,
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'email'      => 'admin@anka.dev',
            'password'   => 'password',
            'system_role' => 'owner',
            'app_role'   => 'Admin',
        ]);
    }
}
