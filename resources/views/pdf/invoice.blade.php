<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $booking->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #212529; }
        h1 { font-size: 22px; margin: 0; }
        .muted { color: #6c757d; }
        .right { text-align: right; }
        .center { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        .items th { background: #212529; color: #fff; padding: 8px; text-align: left; }
        .items td { padding: 8px; border-bottom: 1px solid #dee2e6; }
        .summary td { padding: 4px 8px; }
        .total { font-size: 13px; font-weight: bold; border-top: 2px solid #212529; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; color: #fff; font-weight: bold; }
        .paid { background: #198754; }
        .unpaid { background: #dc3545; }
        .accent { color: #fd7e14; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>
                <h1>{{ config('bengkel.name') }}</h1>
                <div class="muted">{{ config('bengkel.address') }}<br>{{ config('bengkel.phone') }} · {{ config('bengkel.email') }}</div>
            </td>
            <td class="right">
                <h1 class="accent">INVOICE</h1>
                <div>{{ $booking->code }}</div>
                <div class="muted">{{ now()->translatedFormat('d F Y H:i') }}</div>
                <div style="margin-top:6px">
                    @if ($booking->remainingAmount() === 0)
                        <span class="badge paid">LUNAS</span>
                    @else
                        <span class="badge unpaid">DP DIBAYAR</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table style="margin: 24px 0">
        <tr>
            <td width="50%">
                <div class="muted">Ditagihkan kepada</div>
                <strong>{{ $booking->customer_name }}</strong><br>
                {{ $booking->customer_email }}
            </td>
            <td width="50%">
                <div class="muted">Kendaraan & Jadwal</div>
                <strong>{{ $booking->vehicle_brand }} {{ $booking->vehicle_model }}</strong> ({{ $booking->vehicleTypeLabel() }})<br>
                {{ $booking->plate_number }}<br>
                {{ $booking->booking_date->translatedFormat('d F Y') }}, {{ $booking->time_slot }}
                @if ($booking->mechanic)<br>Mekanik: {{ $booking->mechanic->name }}@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th class="center" width="10%">Qty</th>
                <th class="right" width="20%">Harga</th>
                <th class="right" width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($booking->items as $item)
                <tr>
                    <td>{{ $item->name }}@if ($item->is_additional) <span class="muted">(tambahan)</span>@endif</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">@rupiah($item->price)</td>
                    <td class="right">@rupiah($item->subtotal())</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary" style="margin-top: 12px">
        <tr>
            <td width="60%"></td>
            <td>{{ $booking->status === 'completed' || $booking->status === 'waiting_payment' ? 'Total' : 'Estimasi Total' }}</td>
            <td class="right">@rupiah($booking->total_amount)</td>
        </tr>
        @foreach ($booking->payments->where('status', 'paid') as $payment)
            <tr>
                <td></td>
                <td>{{ $payment->typeLabel() }} ({{ $payment->methodLabel() }})<br><span class="muted">{{ $payment->paid_at->format('d/m/Y H:i') }}</span></td>
                <td class="right">- @rupiah($payment->amount)</td>
            </tr>
        @endforeach
        <tr class="total">
            <td></td>
            <td class="total">Sisa Tagihan</td>
            <td class="right total">@rupiah($booking->remainingAmount())</td>
        </tr>
    </table>

    <p class="muted" style="margin-top: 40px">Terima kasih telah mempercayakan perawatan motor Anda kepada {{ config('bengkel.name') }}. Garansi jasa berlaku 7 hari sejak service selesai.</p>
</body>
</html>
