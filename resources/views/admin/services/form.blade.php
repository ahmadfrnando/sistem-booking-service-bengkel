@extends('layouts.admin')

@section('title', $service->exists ? 'Edit Layanan' : 'Tambah Layanan')

@section('content')
    <a href="{{ route('admin.services.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-4">{{ $service->exists ? 'Edit Layanan' : 'Tambah Layanan' }}</h4>

    <div class="card border-0 shadow-sm col-lg-8">
        <div class="card-body p-4">
            <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}">
                @csrf
                @if ($service->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nama Layanan</label>
                        <input type="text" name="name" value="{{ old('name', $service->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="category" value="{{ old('category', $service->category) }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description', $service->description) }}</textarea>
                    </div>
                    @foreach (config('bengkel.vehicle_types') as $key => $label)
                        <div class="col-md-4">
                            <label class="form-label">Harga {{ $label }}</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price_{{ $key }}" value="{{ old('price_'.$key, $service->{'price_'.$key}) }}" min="0" class="form-control" placeholder="Kosongkan jika tidak tersedia">
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-4">
                        <label class="form-label">Durasi (menit)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes) }}" min="5" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $service->is_active))>
                            <label class="form-check-label" for="isActive">Tampilkan di katalog</label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-dark mt-4">Simpan</button>
            </form>
        </div>
    </div>
@endsection
