<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('bookings:expire', function () {
    $count = Booking::where('status', 'pending_payment')
        ->where('updated_at', '<', now()->subMinutes(config('bengkel.payment_expiry_minutes')))
        ->update(['status' => 'expired']);

    $this->info("{$count} booking ditandai kedaluwarsa.");
})->purpose('Tandai booking yang DP-nya tidak dibayar sebagai kedaluwarsa');

Schedule::command('bookings:expire')->everyFiveMinutes();
