@extends('layouts.app')

@section('title', 'Pembayaran DP')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <p class="text-secondary mb-1">Kode Booking</p>
                        <h4 class="fw-bold mb-3">{{ $booking->code }}</h4>

                        <p class="text-secondary mb-1">Total DP ({{ config('bengkel.dp_percent') }}%)</p>
                        <div class="display-6 fw-bold mb-4">@rupiah($booking->dp_amount)</div>

                        @if ($payment)
                            <div class="border rounded-3 p-3 d-inline-block bg-white mb-3">
                                {!! QrCode::size(240)->margin(1)->generate($payment->qr_string) !!}
                            </div>
                            <p class="small text-secondary mb-1">Scan dengan aplikasi e-wallet / m-banking yang mendukung QRIS.</p>
                            <p class="small mb-4">Berlaku sampai <strong>{{ $payment->expires_at->format('H:i') }} WIB</strong></p>

                            <div class="d-flex align-items-center justify-content-center gap-2 text-secondary small" id="waiting">
                                <span class="spinner-border spinner-border-sm"></span> Menunggu pembayaran...
                            </div>

                            @if (config('services.xendit.test_mode'))
                                <form method="POST" action="{{ route('booking.simulate', $booking) }}" class="mt-4">
                                    @csrf
                                    <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-bug"></i> Simulasi Bayar (Test Mode)</button>
                                </form>
                            @endif
                        @else
                            <div class="alert alert-warning small">QR belum tersedia atau sudah kedaluwarsa.</div>
                            <form method="POST" action="{{ route('booking.qris', $booking) }}">
                                @csrf
                                <button class="btn btn-warning fw-semibold"><i class="bi bi-qr-code"></i> Buat QR Baru</button>
                            </form>
                        @endif
                    </div>
                    <div class="card-footer bg-white text-start small p-4">
                        <div class="d-flex justify-content-between"><span class="text-secondary">Jadwal</span><span>{{ $booking->booking_date->translatedFormat('d M Y') }}, {{ $booking->time_slot }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-secondary">Motor</span><span>{{ $booking->vehicle_brand }} {{ $booking->vehicle_model }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-secondary">Estimasi Total</span><span>@rupiah($booking->estimated_total)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($payment)
    @push('scripts')
        <script>
            const statusUrl = @json(route('booking.status', $booking));
            const poll = setInterval(async () => {
                const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (data.payment_status === 'paid' || data.payment_status === 'expired') {
                    clearInterval(poll);
                    window.location.href = data.payment_status === 'paid' ? data.redirect : window.location.href;
                }
            }, 5000);
        </script>
    @endpush
@endif
