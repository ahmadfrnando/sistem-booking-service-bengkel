<?php

return [
    'name' => env('BENGKEL_NAME', 'Chandra Motor'),
    'tagline' => 'Servis motor matic, manual & sport — cepat, jujur, bergaransi.',
    'address' => 'Jl. Raya Pahlawan No. 21, Bandung',
    'phone' => '0812-3456-7890',
    'email' => 'admin@chandramotor.test',
    'open_hours' => 'Senin - Sabtu, 08:00 - 17:00',
    'dp_percent' => 30,
    'payment_expiry_minutes' => 30,
    'slot_capacity' => 3,
    'slot_cutoff_minutes' => 30,
    'closed_days' => [0],
    'time_slots' => [
        '08:00-10:00',
        '10:00-12:00',
        '13:00-15:00',
        '15:00-17:00',
    ],
    'vehicle_types' => [
        'matic' => 'Matic',
        'manual' => 'Manual / Bebek',
        'sport' => 'Sport',
    ],
];
