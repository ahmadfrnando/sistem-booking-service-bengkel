@extends('layouts.app')

@section('title', 'Detail Booking '.$booking->code)

@section('content')
    @php
        $steps = ['confirmed' => 'DP Dibayar', 'checked_in' => 'Datang ke Bengkel', 'in_service' => 'Dikerjakan', 'waiting_payment' => 'Menunggu Pelunasan', 'completed' => 'Selesai'];
        $currentIndex = array_search($booking->status, array_keys($steps));
    @endphp
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <p class="text-secondary mb-0">Kode Booking</p>
                        <h2 class="fw-bold mb-0">{{ $booking->code }}</h2>
                    </div>
                    <span class="badge text-bg-{{ $booking->statusColor() }} fs-6">{{ $booking->statusLabel() }}</span>
                </div>

                @if ($booking->status === 'pending_payment')
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <span>DP belum dibayar.</span>
                        <a href="{{ route('booking.pay', $booking) }}" class="btn btn-sm btn-dark">Bayar Sekarang</a>
                    </div>
                @elseif ($currentIndex !== false)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row text-center g-2">
                                @foreach ($steps as $key => $label)
                                    <div class="col">
                                        <i class="bi {{ $loop->index <= $currentIndex ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' }} fs-4"></i>
                                        <div class="small {{ $loop->index <= $currentIndex ? 'fw-semibold' : 'text-secondary' }}">{{ $label }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="text-secondary">Customer</div>
                                <div class="fw-semibold">{{ $booking->customer_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary">Jadwal</div>
                                <div class="fw-semibold">{{ $booking->booking_date->translatedFormat('l, d F Y') }} · {{ $booking->time_slot }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary">Motor</div>
                                <div class="fw-semibold">{{ $booking->vehicle_brand }} {{ $booking->vehicle_model }} ({{ $booking->vehicleTypeLabel() }})</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary">Plat Nomor</div>
                                <div class="fw-semibold">{{ $booking->plate_number }}</div>
                            </div>
                            @if ($booking->mechanic)
                                <div class="col-md-6">
                                    <div class="text-secondary">Mekanik</div>
                                    <div class="fw-semibold">{{ $booking->mechanic->name }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @include('partials.invoice-table', ['booking' => $booking])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @if ($booking->paidAmount() > 0)
                        <a href="{{ route('booking.invoice', $booking) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-file-earmark-pdf"></i> Invoice</a>
                    @endif
                    @if ($booking->status === 'completed')
                        <a href="{{ route('booking.receipt', $booking) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-receipt"></i> Struk</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
