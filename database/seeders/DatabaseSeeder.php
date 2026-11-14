<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bengkel.test'],
            ['name' => 'Admin Bengkel', 'password' => 'password'],
        );

        $this->call([
            MechanicSeeder::class,
            ServiceSeeder::class,
        ]);
    }
}
