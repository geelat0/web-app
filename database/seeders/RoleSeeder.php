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
                'name'=> 'IT',
                'created_by'=> 'IT',

            ]
          );
        Role::create(
            [
                'name'=> 'Admin',
                'created_by'=> 'IT',

            ]
          );
        Role::create(
            [
                'name'=> 'Budget Office',
                'created_by'=> 'IT',

            ]
          );
        Role::create(
            [
                'name'=> 'Planning',
                'created_by'=> 'IT',

            ]
          );
    }
}
