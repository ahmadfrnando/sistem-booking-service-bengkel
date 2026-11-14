<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Booking Service') · {{ config('bengkel.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-tools text-warning me-1"></i> {{ config('bengkel.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#tentang">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('services') }}">Layanan & Harga</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#kontak">Kontak</a></li>
                    <li class="nav-item"><a class="btn btn-warning btn-sm fw-semibold ms-lg-2" href="{{ route('booking.create') }}">Booking Sekarang</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @include('partials.alerts')
        @yield('content')
    </main>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small">
            <span>&copy; {{ date('Y') }} {{ config('bengkel.name') }}</span>
            <span><i class="bi bi-geo-alt"></i> {{ config('bengkel.address') }}</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
