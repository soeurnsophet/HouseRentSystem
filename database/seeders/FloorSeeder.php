<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = Building::pluck('id');

        for ($i = 1; $i <= 20; $i++) {
            Floor::create([
                'building_id' => $buildings->random(),
                'base_price' => rand(10, 100),
                'name' => 'Floor ' . $i,
                'description' => 'Description for Floor ' . $i,
            ]);
        }
    }
}
