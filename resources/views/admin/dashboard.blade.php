@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h4 class="fw-bold mb-4">Dashboard <small class="text-secondary fs-6">{{ today()->translatedFormat('l, d F Y') }}</small></h4>

    <div class="row g-3 mb-4">
        @foreach ($stats as $label => $value)
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-secondary small">{{ $label }}</div>
                        <div class="fs-3 fw-bold">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold py-3">Jadwal Hari Ini</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Jam</th><th>Kode</th><th>Customer</th><th>Motor</th><th>Mekanik</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($today as $booking)
                        <tr>
                            <td>{{ $booking->time_slot }}</td>
                            <td class="fw-semibold">{{ $booking->code }}</td>
                            <td>{{ $booking->customer_name }}</td>
                            <td>{{ $booking->vehicle_brand }} {{ $booking->vehicle_model }}<br><small class="text-secondary">{{ $booking->plate_number }}</small></td>
                            <td>{{ $booking->mechanic?->name ?? '-' }}</td>
                            <td><span class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-dark">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada booking untuk hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
