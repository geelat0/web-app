<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 'filter_dashboard',
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_organizational_outcome',
            'manage_indicator',
            'manage_history',
            'manage_entries',
            'access_pending_entries',
            'access_report_generation',

            // Add all other necessary permissions
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
