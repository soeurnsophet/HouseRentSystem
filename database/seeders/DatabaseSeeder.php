<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory(500)->create();

        // $this->call([
        //     GenderSeeder::class,
        // ]);

        User::factory()->create([
            'name' => 'Soeurn Sophet',
            'username' => 'sophet',
            'password' => Hash::make('phet123'),
            'phone' => '081638188',
            'email' => 'soeurnsophet@gmail.com',
            'role' => 'admin',
            'gender_id' => 1,
        ]);
        User::factory()->create([
            'name' => 'Nem Rinda',
            'username' => 'darinda',
            'password' => Hash::make('da123'),
            'phone' => '081638189',
            'email' => 'nemrinda@gmail.com',
            'role' => 'manager',
            'gender_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'Noy Vannak',
            'username' => 'vannak',
            'password' => Hash::make('nak123'),
            'phone' => '081638199',
            'email' => 'noyvannak@gmail.com',
            'role' => 'user',
            'gender_id' => 1,
        ]);

        // $this->call([
        //     FloorSeeder::class,
        // ]);
    }
}
