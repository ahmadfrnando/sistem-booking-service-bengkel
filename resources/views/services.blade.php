@extends('layouts.app')

@section('title', 'Layanan & Harga')

@section('content')
    <div class="container py-5">
        <h1 class="fw-bold">Layanan & Price List</h1>
        <p class="text-secondary">Harga jasa per jenis motor. Sparepart dihitung terpisah dan dikonfirmasi saat servis.</p>

        <ul class="nav nav-pills my-4 gap-2">
            <li class="nav-item"><a class="nav-link {{ $type ? 'text-dark' : 'active bg-dark' }}" href="{{ route('services') }}">Semua</a></li>
            @foreach ($types as $key => $label)
                <li class="nav-item"><a class="nav-link {{ $type === $key ? 'active bg-dark' : 'text-dark' }}" href="{{ route('services', ['type' => $key]) }}">{{ $label }}</a></li>
            @endforeach
        </ul>

        @forelse ($services as $category => $items)
            <h5 class="fw-semibold mt-4 mb-3">{{ $category }}</h5>
            <div class="row g-4">
                @foreach ($items as $service)
                    <div class="col-md-6 col-lg-4">
                        @include('partials.service-card')
                    </div>
                @endforeach
            </div>
        @empty
            <div class="alert alert-light border">Belum ada layanan.</div>
        @endforelse
    </div>
@endsection
