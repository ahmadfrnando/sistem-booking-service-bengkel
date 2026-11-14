@extends('layouts.admin')

@section('title', 'Layanan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Layanan & Harga</h4>
        <a href="{{ route('admin.services.create') }}" class="btn btn-dark"><i class="bi bi-plus"></i> Tambah</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Layanan</th><th>Kategori</th>@foreach (config('bengkel.vehicle_types') as $label)<th class="text-end">{{ $label }}</th>@endforeach<th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td class="fw-semibold">{{ $service->name }}<br><small class="text-secondary fw-normal">± {{ $service->duration_minutes }} menit</small></td>
                            <td>{{ $service->category }}</td>
                            @foreach (array_keys(config('bengkel.vehicle_types')) as $type)
                                <td class="text-end">
                                    @if ($service->priceFor($type) !== null) @rupiah($service->priceFor($type)) @else <span class="text-secondary">-</span> @endif
                                </td>
                            @endforeach
                            <td><span class="badge text-bg-{{ $service->is_active ? 'success' : 'secondary' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="d-inline" onsubmit="return confirm('Hapus layanan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
