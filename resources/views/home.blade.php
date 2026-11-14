@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <section class="bg-dark text-white py-5">
        <div class="container py-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge text-bg-warning mb-3">Matic · Manual · Sport</span>
                    <h1 class="display-5 fw-bold">Servis motor tanpa antre, cukup booking online.</h1>
                    <p class="lead text-white-50 my-4">{{ config('bengkel.tagline') }} Pilih layanan, tentukan jadwal, bayar DP {{ config('bengkel.dp_percent') }}% via QRIS, lalu datang sesuai jam yang dipilih.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('booking.create') }}" class="btn btn-warning btn-lg fw-semibold">Booking Service</a>
                        <a href="{{ route('services') }}" class="btn btn-outline-light btn-lg">Lihat Price List</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3 text-center">
                        @foreach ([['bi-calendar-check', 'Pilih Jadwal'], ['bi-qr-code', 'Bayar DP QRIS'], ['bi-envelope', 'Notifikasi Email'], ['bi-receipt', 'Invoice Digital']] as [$icon, $label])
                            <div class="col-6">
                                <div class="border border-secondary rounded-3 p-4">
                                    <i class="bi {{ $icon }} fs-2 text-warning"></i>
                                    <div class="small mt-2">{{ $label }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="tentang" class="py-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-3">Tentang {{ config('bengkel.name') }}</h2>
                    <p class="text-secondary">Bengkel umum untuk motor matic, bebek/manual, dan sport. Ditangani mekanik berpengalaman dengan peralatan lengkap dan harga jasa yang transparan sejak awal booking.</p>
                    <ul class="list-unstyled text-secondary">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Harga jasa jelas sebelum servis</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Konfirmasi biaya tambahan sebelum dikerjakan</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Garansi jasa 7 hari</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6"><div class="card border-0 shadow-sm text-center p-4"><div class="fs-2 fw-bold">8+</div><div class="small text-secondary">Tahun berpengalaman</div></div></div>
                        <div class="col-6"><div class="card border-0 shadow-sm text-center p-4"><div class="fs-2 fw-bold">5</div><div class="small text-secondary">Mekanik aktif</div></div></div>
                        <div class="col-6"><div class="card border-0 shadow-sm text-center p-4"><div class="fs-2 fw-bold">3</div><div class="small text-secondary">Jenis motor</div></div></div>
                        <div class="col-6"><div class="card border-0 shadow-sm text-center p-4"><div class="fs-2 fw-bold">7 hari</div><div class="small text-secondary">Garansi jasa</div></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Layanan Populer</h2>
                    <p class="text-secondary mb-0">Harga jasa, belum termasuk sparepart kecuali disebutkan.</p>
                </div>
                <a href="{{ route('services') }}" class="btn btn-outline-dark d-none d-md-inline-block">Semua Layanan</a>
            </div>
            <div class="row g-4">
                @foreach ($services as $service)
                    <div class="col-md-6 col-lg-4">
                        @include('partials.service-card')
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="kontak" class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4"><i class="bi bi-geo-alt fs-3 text-warning"></i><h6 class="fw-semibold mt-2">Alamat</h6><p class="text-secondary mb-0">{{ config('bengkel.address') }}</p></div>
                <div class="col-md-4"><i class="bi bi-clock fs-3 text-warning"></i><h6 class="fw-semibold mt-2">Jam Operasional</h6><p class="text-secondary mb-0">{{ config('bengkel.open_hours') }}</p></div>
                <div class="col-md-4"><i class="bi bi-telephone fs-3 text-warning"></i><h6 class="fw-semibold mt-2">Kontak</h6><p class="text-secondary mb-0">{{ config('bengkel.phone') }}<br>{{ config('bengkel.email') }}</p></div>
            </div>
        </div>
    </section>
@endsection
