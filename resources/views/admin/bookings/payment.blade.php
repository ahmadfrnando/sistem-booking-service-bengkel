@extends('layouts.admin')

@section('title', 'Pembayaran '.$booking->code)

@section('content')
    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-4">Pelunasan · {{ $booking->code }}</h4>

    <div class="row g-4">
        <div class="col-lg-7">
            @include('partials.invoice-table', ['booking' => $booking])
        </div>

        <div class="col-lg-5">
            @if ($payment)
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body p-4">
                        <p class="text-secondary mb-1">Scan QRIS untuk membayar</p>
                        <div class="display-6 fw-bold mb-3">@rupiah($payment->amount)</div>
                        <div class="border rounded-3 p-3 d-inline-block bg-white mb-3">
                            {!! QrCode::size(280)->margin(1)->generate($payment->qr_string) !!}
                        </div>
                        <p class="small mb-3">Berlaku sampai {{ $payment->expires_at->format('H:i') }} WIB</p>
                        <div class="text-secondary small"><span class="spinner-border spinner-border-sm"></span> Menunggu pembayaran...</div>

                        @if (config('services.xendit.test_mode'))
                            <form method="POST" action="{{ route('admin.bookings.payment.simulate', $booking) }}" class="mt-3">
                                @csrf
                                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-bug"></i> Simulasi Bayar (Test Mode)</button>
                            </form>
                        @endif
                    </div>
                    <div class="card-footer bg-white">
                        <a href="#manual" data-bs-toggle="collapse" class="small">Bayar manual saja</a>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold">QRIS</h6>
                        <p class="small text-secondary">Tampilkan QR di layar untuk discan customer.</p>
                        <form method="POST" action="{{ route('admin.bookings.payment.qris', $booking) }}">
                            @csrf
                            <button class="btn btn-warning w-100 fw-semibold"><i class="bi bi-qr-code"></i> Tampilkan QRIS @rupiah($booking->remainingAmount())</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm {{ $payment ? 'collapse mt-4' : '' }}" id="manual">
                <div class="card-body p-4">
                    <h6 class="fw-semibold">Pembayaran Manual</h6>
                    <form method="POST" action="{{ route('admin.bookings.payment.manual', $booking) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small">Metode</label>
                            <select name="method" class="form-select" id="method">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <div class="mb-3" id="receivedGroup">
                            <label class="form-label small">Uang Diterima</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="received_amount" value="{{ old('received_amount', $booking->remainingAmount()) }}" min="{{ $booking->remainingAmount() }}" class="form-control text-end">
                            </div>
                        </div>
                        <button class="btn btn-dark w-100" onclick="return confirm('Konfirmasi pembayaran sudah diterima?')">Konfirmasi Lunas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const method = document.getElementById('method');
        method.addEventListener('change', () => document.getElementById('receivedGroup').classList.toggle('d-none', method.value !== 'cash'));

        @if ($payment)
            const poll = setInterval(async () => {
                const response = await fetch(@json(route('admin.bookings.payment.status', $booking)), { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (data.payment_status === 'paid') {
                    clearInterval(poll);
                    window.location.href = data.redirect;
                } else if (data.payment_status === 'expired') {
                    clearInterval(poll);
                    window.location.reload();
                }
            }, 4000);
        @endif
    </script>
@endpush
