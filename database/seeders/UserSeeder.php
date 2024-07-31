<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create(
            [
                'first_name' => 'Angelica',
                'middle_name' => 'Lianko',
                'last_name' => 'Bonganay',
                'user_name' => 'A.bong',
                'province' => 'Albay',
                'position' => 'Developer',
                'mobile_number' => '09123456789',
                'email' => 'angelicamae.bonganay@gmail.com',
                'role_id' => '1',
                'division_id' => '["1"]',
                'status' => 'Active',
                'password' => Hash::make('123456'),
                'created_by' => 'A.bong',
            ]
          );
    }
}
