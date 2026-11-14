<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Booking::whereDate('booking_date', today())
            ->whereNotIn('status', ['pending_payment', 'expired', 'cancelled'])
            ->with('mechanic')
            ->orderBy('time_slot')
            ->get();

        $stats = [
            'Booking Hari Ini' => $today->count(),
            'Sedang Dikerjakan' => Booking::where('status', 'in_service')->count(),
            'Menunggu Pelunasan' => Booking::where('status', 'waiting_payment')->count(),
            'Pendapatan Hari Ini' => 'Rp '.number_format(
                Payment::where('status', 'paid')->whereDate('paid_at', today())->sum('amount'), 0, ',', '.'
            ),
        ];

        return view('admin.dashboard', compact('today', 'stats'));
    }
}
