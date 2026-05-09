<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gender::create([
            'gender_kh' => 'ប',
            'gender_kh_full' => 'ប្រុស',
            'gender_en' => 'M',
            'gender_en_full' => 'Male',
        ]);

        Gender::create([
            'gender_kh' => 'ស',
            'gender_kh_full' => 'ស្រី',
            'gender_en' => 'F',
            'gender_en_full' => 'Female',
        ]);
    }
}
