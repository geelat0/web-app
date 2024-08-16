<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Division::create(
            [
                'division_name'=> 'IMSD',
                'created_by'=> 'IT',
                
            ]
          );

          Division::create(
            [
                'division_name'=> 'TSSD',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'Albay PO',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'Camarines Sur PO',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'Camarines Norte PO',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'Catanduanes PO',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'Masbate PO',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'Sorsogon PO',
                'created_by'=> 'IT',
                
            ]
          );
          Division::create(
            [
                'division_name'=> 'RTWPB',
                'created_by'=> 'IT',
                
            ]
          );
    }
}
