<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $booking->code }}</title>
    <style>
        @page { margin: 10px; }
        body { font-family: DejaVu Sans Mono, monospace; font-size: 9px; color: #000; }
        .center { text-align: center; }
        .right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold" style="font-size: 11px">{{ strtoupper(config('bengkel.name')) }}</div>
        <div>{{ config('bengkel.address') }}</div>
        <div>{{ config('bengkel.phone') }}</div>
    </div>
    <div class="line"></div>
    <table>
        <tr><td>No</td><td class="right">{{ $booking->code }}</td></tr>
        <tr><td>Tanggal</td><td class="right">{{ $payment->paid_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Customer</td><td class="right">{{ $booking->customer_name }}</td></tr>
        <tr><td>Plat</td><td class="right">{{ $booking->plate_number }}</td></tr>
    </table>
    <div class="line"></div>
    <table>
        @foreach ($booking->items as $item)
            <tr><td colspan="2">{{ $item->name }}</td></tr>
            <tr><td>{{ $item->qty }} x {{ number_format($item->price, 0, ',', '.') }}</td><td class="right">{{ number_format($item->subtotal(), 0, ',', '.') }}</td></tr>
        @endforeach
    </table>
    <div class="line"></div>
    <table>
        <tr class="bold"><td>TOTAL</td><td class="right">{{ number_format($booking->total_amount, 0, ',', '.') }}</td></tr>
        @foreach ($booking->payments->where('status', 'paid') as $paid)
            <tr><td>{{ $paid->type === 'dp' ? 'DP' : 'Bayar' }} ({{ $paid->methodLabel() }})</td><td class="right">{{ number_format($paid->amount, 0, ',', '.') }}</td></tr>
        @endforeach
        @if ($payment->method === 'cash' && $payment->received_amount)
            <tr><td>Tunai Diterima</td><td class="right">{{ number_format($payment->received_amount, 0, ',', '.') }}</td></tr>
            <tr><td>Kembali</td><td class="right">{{ number_format($payment->received_amount - $payment->amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="bold"><td>SISA</td><td class="right">{{ number_format($booking->remainingAmount(), 0, ',', '.') }}</td></tr>
    </table>
    <div class="line"></div>
    <div class="center">*** LUNAS ***<br>Terima kasih atas kunjungan Anda</div>
</body>
</html>
