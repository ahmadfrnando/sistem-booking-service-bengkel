<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['Servis Berkala', 'Servis Ringan', 'Cek & setel rem, rantai/CVT, busi, filter udara, aki dan kelistrikan dasar.', 60, 75000, 70000, 120000],
            ['Servis Berkala', 'Servis Lengkap (Tune Up)', 'Servis ringan + bersih injeksi/karburator, throttle body, CVT/rantai dan setel klep.', 120, 150000, 135000, 250000],
            ['Servis Berkala', 'Ganti Oli Mesin', 'Jasa + oli standar (matic 0,8 L, manual 1 L, sport 1,2 L).', 15, 60000, 55000, 95000],
            ['Mesin & Transmisi', 'Servis CVT', 'Bongkar, bersihkan dan lumasi CVT, cek v-belt, roller dan kampas ganda.', 60, 75000, null, null],
            ['Mesin & Transmisi', 'Ganti Oli Gardan', 'Jasa + oli gardan 120 ml.', 10, 25000, null, null],
            ['Mesin & Transmisi', 'Servis Rantai & Gear Set', 'Bersihkan, lumasi dan setel ketegangan rantai.', 20, null, 30000, 45000],
            ['Mesin & Transmisi', 'Bersih Injeksi & Throttle Body', 'Pembersihan injektor dan throttle body dengan cairan khusus.', 45, 60000, 60000, 90000],
            ['Mesin & Transmisi', 'Setel Klep', 'Penyetelan celah klep sesuai spesifikasi pabrikan.', 45, 50000, 50000, 100000],
            ['Mesin & Transmisi', 'Turun Mesin (Overhaul)', 'Jasa bongkar pasang mesin, belum termasuk sparepart.', 480, 600000, 550000, 1200000],
            ['Rem & Kaki-kaki', 'Servis Rem', 'Bersihkan dan setel kaliper/tromol depan & belakang.', 30, 40000, 40000, 60000],
            ['Rem & Kaki-kaki', 'Ganti Kampas Rem', 'Jasa pasang kampas rem per roda, kampas dihitung terpisah.', 20, 20000, 20000, 35000],
            ['Rem & Kaki-kaki', 'Kuras Minyak Rem', 'Ganti minyak rem dan buang angin sistem hidrolik.', 30, 35000, 35000, 50000],
            ['Rem & Kaki-kaki', 'Pasang / Ganti Ban', 'Jasa bongkar pasang ban per roda.', 15, 15000, 15000, 25000],
            ['Kelistrikan & Pendingin', 'Servis Kelistrikan', 'Cek aki, kiprok, spul, lampu dan kabel body.', 45, 50000, 50000, 75000],
            ['Kelistrikan & Pendingin', 'Kuras Radiator', 'Jasa + coolant untuk motor berpendingin cairan.', 30, 55000, 55000, 75000],
            ['Kelistrikan & Pendingin', 'Scan & Reset ECU', 'Diagnosa kode error dan reset ECU dengan scanner.', 20, 50000, 50000, 75000],
        ];

        foreach ($services as [$category, $name, $description, $duration, $matic, $manual, $sport]) {
            Service::updateOrCreate(['name' => $name], [
                'category' => $category,
                'description' => $description,
                'duration_minutes' => $duration,
                'price_matic' => $matic,
                'price_manual' => $manual,
                'price_sport' => $sport,
                'is_active' => true,
            ]);
        }
    }
}
