<x-mail::message>
# Booking Terkonfirmasi

Halo **{{ $booking->customer_name }}**,

Pembayaran DP booking service di **{{ config('bengkel.name') }}** sudah kami terima.

<x-mail::table>
| | |
|:--|--:|
| Kode Booking | **{{ $booking->code }}** |
| Motor | {{ $booking->vehicle_brand }} {{ $booking->vehicle_model }} ({{ $booking->plate_number }}) |
| Jadwal | {{ $booking->booking_date->translatedFormat('l, d F Y') }}, {{ $booking->time_slot }} |
| DP Dibayar | Rp {{ number_format($payment->amount, 0, ',', '.') }} |
| Sisa Estimasi | Rp {{ number_format($booking->remainingAmount(), 0, ',', '.') }} |
</x-mail::table>

Silakan datang sesuai jadwal dan tunjukkan kode booking ke admin. Invoice DP terlampir dalam email ini.

<x-mail::button :url="route('booking.show', $booking)" color="success">
Lihat Detail Booking
</x-mail::button>

Salam,<br>
{{ config('bengkel.name') }}<br>
{{ config('bengkel.address') }}
</x-mail::message>
