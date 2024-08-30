<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $divisionIds = Division::pluck('id')->toArray();
        $divisionIdsJson = json_encode($divisionIds);
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
                'division_id' => $divisionIdsJson,
                'status' => 'Active',
                'expiration_date' => Carbon::now()->addDays(90),
                'password' => Hash::make('123456'),
                'created_by' => 'A.bong',
            ]
          );
        User::create(
            [
                'first_name' => 'Ana',
                'middle_name' => 'Smith',
                'last_name' => 'Doe',
                'user_name' => 'A.doe',
                'province' => 'Albay',
                'position' => 'Developer',
                'mobile_number' => '09123456789',
                'email' => 'anadoe@gmail.com',
                'role_id' => '2',
                'division_id' => $divisionIdsJson,
                'status' => 'Active',
                'expiration_date' => Carbon::now()->addDays(90),
                'password' => Hash::make('123456'),
                'created_by' => 'A.bong',
            ]
          );
        User::create(
            [
                'first_name' => 'John',
                'middle_name' => 'Smith',
                'last_name' => 'Doe',
                'user_name' => 'J.doe',
                'province' => 'Albay',
                'position' => 'Developer',
                'mobile_number' => '09123456789',
                'email' => 'johndoe@gmail.com',
                'role_id' => '3',
                'division_id' => '["1", "2", "3", "4", "5", "6", "7", "8", "9"]',
                'status' => 'Active',
                'expiration_date' => Carbon::now()->addDays(90),
                'password' => Hash::make('123456'),
                'created_by' => 'A.bong',
            ]
          );
        User::create(
            [
                'first_name' => 'Carl',
                'middle_name' => 'Smith',
                'last_name' => 'Doe',
                'user_name' => 'C.doe',
                'province' => 'Albay',
                'position' => 'Developer',
                'mobile_number' => '09123456789',
                'email' => 'carldoe@gmail.com',
                'role_id' => '4',
                'division_id' => '["1", "2", "3", "4", "5", "6", "7", "8", "9"]',
                'status' => 'Active',
                'expiration_date' => Carbon::now()->addDays(90),
                'password' => Hash::make('123456'),
                'created_by' => 'A.bong',
            ]
          );
    }
}
