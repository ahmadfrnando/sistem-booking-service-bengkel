<?php

namespace Database\Seeders;

use App\Models\Mechanic;
use Illuminate\Database\Seeder;

class MechanicSeeder extends Seeder
{
    public function run(): void
    {
        $mechanics = [
            ['name' => 'Budi Santoso', 'phone' => '081234500001', 'specialty' => 'Matic & CVT'],
            ['name' => 'Agus Setiawan', 'phone' => '081234500002', 'specialty' => 'Injeksi & Kelistrikan'],
            ['name' => 'Rudi Hartono', 'phone' => '081234500003', 'specialty' => 'Motor Sport'],
            ['name' => 'Dedi Kurniawan', 'phone' => '081234500004', 'specialty' => 'Bebek & Manual'],
            ['name' => 'Yoga Pratama', 'phone' => '081234500005', 'specialty' => 'Rem & Kaki-kaki'],
        ];

        foreach ($mechanics as $mechanic) {
            Mechanic::updateOrCreate(['name' => $mechanic['name']], $mechanic);
        }
    }
}
