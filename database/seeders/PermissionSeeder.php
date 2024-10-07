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
            'manage_pending_entries',
            'generate_report',

            // Add all other necessary permissions
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
