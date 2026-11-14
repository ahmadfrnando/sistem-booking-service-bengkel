@extends('layouts.admin')

@section('title', 'Booking')

@section('content')
    <h4 class="fw-bold mb-4">Data Booking</h4>

    <form class="row g-2 mb-3">
        <div class="col-md-4"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode, nama, email, plat..."></div>
        <div class="col-md-3"><input type="date" name="date" value="{{ request('date') }}" class="form-control"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Semua status</option>
                @foreach ($statuses as $key => [$label])
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid"><button class="btn btn-dark">Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Kode</th><th>Jadwal</th><th>Customer</th><th>Motor</th><th class="text-end">Total</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="fw-semibold">{{ $booking->code }}</td>
                            <td>{{ $booking->booking_date->format('d/m/Y') }}<br><small class="text-secondary">{{ $booking->time_slot }}</small></td>
                            <td>{{ $booking->customer_name }}<br><small class="text-secondary">{{ $booking->customer_email }}</small></td>
                            <td>{{ $booking->vehicle_brand }} {{ $booking->vehicle_model }}<br><small class="text-secondary">{{ $booking->plate_number }}</small></td>
                            <td class="text-end">@rupiah($booking->total_amount)</td>
                            <td><span class="badge text-bg-{{ $booking->statusColor() }}">{{ $booking->statusLabel() }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-dark">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $bookings->links() }}</div>
@endsection
