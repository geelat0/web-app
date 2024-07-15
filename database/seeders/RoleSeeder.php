<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(
            [
                'name'=> 'SuperAdmin',
                'created_by'=> 'SuperAdmin',
                
            ]
          );

          Role::create(
            [
                'name'=> 'User',
                'created_by'=> 'SuperAdmin',
                
            ]
          );
    }
}
