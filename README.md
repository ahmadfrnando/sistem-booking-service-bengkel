# Sistem Booking Service Bengkel Motor

Laravel 12 · PHP 8.2 · MySQL · Bootstrap 5.3 (CDN) · Xendit Payments API v3 (QRIS) · Email (Laravel Mail) · DomPDF

## Alur

1. Customer buka landing page → lihat price list (`/layanan`)
2. Isi form booking (`/booking`): data customer (email wajib), layanan, jenis motor, tanggal & slot jam
3. Bayar DP 30% via QRIS (Xendit) → status booking `Terkonfirmasi`
4. Email konfirmasi + lampiran invoice DP (PDF) terkirim otomatis
5. Customer datang → admin verifikasi data & check-in
6. Admin menugaskan mekanik → `Dikerjakan`
7. Service selesai → admin input biaya jasa + biaya tambahan → invoice terbit
8. Pelunasan via QRIS (QR tampil di layar admin) atau manual (tunai/transfer)
9. Email berisi lampiran invoice + struk (PDF) terkirim ke customer

## Struktur

```
app/Http/Controllers/
├── Concerns/
│   ├── HandlesPayments.php       buat QRIS, sinkron status, tandai lunas, kirim email + PDF
│   └── InteractsWithXendit.php   HTTP client Xendit v3 /payment_requests
├── Admin/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── BookingController.php     check-in, mekanik, selesai, pelunasan
│   ├── ServiceController.php     CRUD price list
│   └── MechanicController.php
├── HomeController.php            landing + katalog
├── BookingController.php         form booking, bayar DP, status, invoice/struk
└── XenditWebhookController.php
app/Mail/                         BookingConfirmedMail (DP), BookingCompletedMail (lunas)
config/bengkel.php                profil bengkel, slot jam, kapasitas, DP %
resources/views/emails/           template email (markdown)
resources/views/pdf/              template invoice & struk
```

## Menjalankan dengan Sail (development)

```bash
cp .env.example .env
```

Sesuaikan `.env` untuk Sail: `DB_HOST=mysql`, `DB_USERNAME=sail`, `DB_PASSWORD=password`.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

Buka http://localhost

## Menjalankan di Laragon (PHP 8.2)

1. Pastikan PHP Laragon 8.2+ dan ekstensi `gd`, `mbstring`, `dom`, `pdo_mysql` aktif
2. Clone ke `C:\laragon\www\bengkel`
3. Buat database `bengkel_booking`
4. Jalankan:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Buka `http://bengkel.test`. `composer.json` dikunci ke `platform.php = 8.2.0`, jadi dependency tetap kompatibel dengan PHP 8.2.

## Login Admin

`/admin/login` · `admin@bengkel.test` / `password`. Ganti password setelah login pertama.

## Konfigurasi `.env`

| Key | Keterangan |
| --- | --- |
| `XENDIT_SECRET_KEY` | Secret key mode **Test** dari Dashboard Xendit → Settings → API Keys (permission Money-in: Write) |
| `XENDIT_WEBHOOK_TOKEN` | Verification token dari Dashboard Xendit → Settings → Webhooks |
| `XENDIT_TEST_MODE` | `true` menampilkan tombol "Simulasi Bayar" |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` | SMTP pengirim email (Gmail App Password, Mailtrap, dsb.) |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Alamat & nama pengirim |

### Webhook Xendit

Set URL webhook event **Payments** (`payment.capture`) ke `https://<domain>/webhooks/xendit`. Di lokal, pakai ngrok / Postman webhook forwarding:

```bash
ngrok http 80
```

Tanpa webhook pun status tetap terupdate: halaman pembayaran polling ke `GET /v3/payment_requests/{id}` tiap beberapa detik.

### Email

Default `MAIL_MAILER=log`: email tidak benar-benar terkirim, isinya ditulis ke `storage/logs/laravel.log`. Isi konfigurasi SMTP di `.env` untuk mengirim sungguhan. Kalau pengiriman gagal, error dicatat di log, proses booking tetap jalan, dan admin bisa kirim ulang lewat tombol **Kirim Ulang Email** di detail booking.

## Scheduler

Booking yang DP-nya tidak dibayar dalam 30 menit ditandai kedaluwarsa:

```bash
php artisan schedule:work
```

Slot yang ditahan booking belum bayar juga otomatis dilepas setelah 30 menit walau scheduler tidak jalan.
# sistem-booking-service-bengkel
