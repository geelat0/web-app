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
                'first_name' => 'angelica',
                'middle_name' => 'lianko',
                'last_name' => 'bonganay',
                'province' => 'albay',
                'position' => 'Developer',
                'mobile_number' => '09123456789',
                'email' => 'angelicamae.bonganay@gmail.com',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('123456'),
            ]
          );
    }
}
