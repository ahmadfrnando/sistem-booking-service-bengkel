<x-mail::message>
# Service Selesai & Lunas

Halo **{{ $booking->customer_name }}**,

Service motor Anda di **{{ config('bengkel.name') }}** sudah selesai dan lunas. Terima kasih atas kepercayaan Anda!

<x-mail::table>
| | |
|:--|--:|
| Kode Booking | **{{ $booking->code }}** |
| Motor | {{ $booking->vehicle_brand }} {{ $booking->vehicle_model }} ({{ $booking->plate_number }}) |
| Total Biaya | Rp {{ number_format($booking->total_amount, 0, ',', '.') }} |
| Metode Pelunasan | {{ $payment->methodLabel() }} |
| Tanggal Lunas | {{ $payment->paid_at->translatedFormat('d F Y H:i') }} |
</x-mail::table>

Invoice dan struk pembayaran terlampir dalam email ini. Garansi jasa berlaku 7 hari sejak service selesai.

Salam,<br>
{{ config('bengkel.name') }}<br>
{{ config('bengkel.address') }}
</x-mail::message>
