@extends('layouts.admin')

@section('title', 'Booking '.$booking->code)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0">{{ $booking->code }}</h4>
        </div>
        <span class="badge text-bg-{{ $booking->statusColor() }} fs-6">{{ $booking->statusLabel() }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-3 small">
                        <div class="col-md-4"><div class="text-secondary">Customer</div><div class="fw-semibold">{{ $booking->customer_name }}</div></div>
                        <div class="col-md-8"><div class="text-secondary">Email</div><div class="fw-semibold">{{ $booking->customer_email }}</div></div>
                        <div class="col-md-4"><div class="text-secondary">Motor</div><div class="fw-semibold">{{ $booking->vehicle_brand }} {{ $booking->vehicle_model }} ({{ $booking->vehicleTypeLabel() }})</div></div>
                        <div class="col-md-4"><div class="text-secondary">Plat Nomor</div><div class="fw-semibold">{{ $booking->plate_number }}</div></div>
                        <div class="col-md-4"><div class="text-secondary">Jadwal</div><div class="fw-semibold">{{ $booking->booking_date->translatedFormat('d M Y') }}, {{ $booking->time_slot }}</div></div>
                        <div class="col-md-4"><div class="text-secondary">Mekanik</div><div class="fw-semibold">{{ $booking->mechanic?->name ?? '-' }}</div></div>
                        <div class="col-md-8"><div class="text-secondary">Keluhan</div><div>{{ $booking->complaint ?? '-' }}</div></div>
                        @if ($booking->admin_notes)
                            <div class="col-12"><div class="text-secondary">Catatan Admin</div><div>{{ $booking->admin_notes }}</div></div>
                        @endif
                    </div>
                </div>
            </div>

            @include('partials.invoice-table', ['booking' => $booking])

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-semibold py-3">Riwayat Pembayaran</div>
                <ul class="list-group list-group-flush small">
                    @forelse ($booking->payments as $payment)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $payment->typeLabel() }} · {{ $payment->methodLabel() }}<br><span class="text-secondary">{{ $payment->reference_id }}</span></span>
                            <span class="text-end">@rupiah($payment->amount)<br>
                                <span class="badge text-bg-{{ $payment->isPaid() ? 'success' : 'secondary' }}">{{ $payment->isPaid() ? 'Lunas '.$payment->paid_at->format('d/m H:i') : ucfirst($payment->status) }}</span>
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-secondary">Belum ada pembayaran.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @switch($booking->status)
                        @case('pending_payment')
                            <h6 class="fw-semibold">Menunggu DP</h6>
                            <p class="small text-secondary">Customer belum membayar DP. Booking otomatis kedaluwarsa setelah {{ config('bengkel.payment_expiry_minutes') }} menit.</p>
                            @break

                        @case('confirmed')
                            <h6 class="fw-semibold mb-3">Verifikasi Kedatangan Customer</h6>
                            <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                                @csrf
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="checkIdentity" required>
                                    <label class="form-check-label small" for="checkIdentity">Nama & email customer sesuai</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="checkVehicle" required>
                                    <label class="form-check-label small" for="checkVehicle">Motor & plat nomor sesuai</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Plat Nomor</label>
                                    <input type="text" name="plate_number" value="{{ $booking->plate_number }}" class="form-control text-uppercase" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Catatan Kondisi Motor</label>
                                    <textarea name="admin_notes" rows="2" class="form-control">{{ $booking->admin_notes }}</textarea>
                                </div>
                                <button class="btn btn-dark w-100">Data Sesuai, Check-in</button>
                            </form>
                            @break

                        @case('checked_in')
                            <h6 class="fw-semibold mb-3">Tugaskan Mekanik</h6>
                            <form method="POST" action="{{ route('admin.bookings.start', $booking) }}">
                                @csrf
                                <select name="mechanic_id" class="form-select mb-3" required>
                                    <option value="">Pilih mekanik</option>
                                    @foreach ($mechanics as $mechanic)
                                        <option value="{{ $mechanic->id }}">{{ $mechanic->name }}{{ $mechanic->specialty ? ' — '.$mechanic->specialty : '' }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-warning w-100 fw-semibold">Mulai Service</button>
                            </form>
                            @break

                        @case('in_service')
                            <h6 class="fw-semibold">Sedang Dikerjakan</h6>
                            <p class="small text-secondary">Oleh {{ $booking->mechanic?->name }} sejak {{ $booking->service_started_at?->format('H:i') }}.</p>
                            <a href="{{ route('admin.bookings.finish', $booking) }}" class="btn btn-success w-100">Selesaikan Service</a>
                            @break

                        @case('waiting_payment')
                            <h6 class="fw-semibold">Menunggu Pelunasan</h6>
                            <p class="small text-secondary">Sisa tagihan @rupiah($booking->remainingAmount())</p>
                            <a href="{{ route('admin.bookings.payment', $booking) }}" class="btn btn-warning w-100 fw-semibold">Proses Pembayaran</a>
                            @break

                        @case('completed')
                            <h6 class="fw-semibold text-success"><i class="bi bi-check-circle"></i> Selesai & Lunas</h6>
                            <p class="small text-secondary">{{ $booking->completed_at?->translatedFormat('d M Y H:i') }}</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('booking.invoice', $booking) }}" target="_blank" class="btn btn-outline-dark">Invoice PDF</a>
                                <a href="{{ route('booking.receipt', $booking) }}" target="_blank" class="btn btn-outline-dark">Struk PDF</a>
                            </div>
                            @break

                        @default
                            <p class="small text-secondary mb-0">Booking ini sudah {{ strtolower($booking->statusLabel()) }}.</p>
                    @endswitch

                    @if (in_array($booking->status, ['pending_payment', 'confirmed']))
                        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="mt-3" onsubmit="return confirm('Batalkan booking ini?')">
                            @csrf
                            <button class="btn btn-link text-danger btn-sm p-0">Batalkan booking</button>
                        </form>
                    @endif

                    @if (in_array($booking->status, ['confirmed', 'completed']))
                        <form method="POST" action="{{ route('admin.bookings.resend', $booking) }}" class="mt-3">
                            @csrf
                            <button class="btn btn-outline-dark btn-sm w-100"><i class="bi bi-envelope"></i> Kirim Ulang Email</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
