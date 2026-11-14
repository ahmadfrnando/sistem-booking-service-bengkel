@extends('layouts.admin')

@section('title', $mechanic->exists ? 'Edit Mekanik' : 'Tambah Mekanik')

@section('content')
    <a href="{{ route('admin.mechanics.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-4">{{ $mechanic->exists ? 'Edit Mekanik' : 'Tambah Mekanik' }}</h4>

    <div class="card border-0 shadow-sm col-lg-6">
        <div class="card-body p-4">
            <form method="POST" action="{{ $mechanic->exists ? route('admin.mechanics.update', $mechanic) : route('admin.mechanics.store') }}">
                @csrf
                @if ($mechanic->exists) @method('PUT') @endif
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $mechanic->name) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" value="{{ old('phone', $mechanic->phone) }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Spesialisasi</label>
                    <input type="text" name="specialty" value="{{ old('specialty', $mechanic->specialty) }}" class="form-control" placeholder="Matic, Injeksi, Kelistrikan...">
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', $mechanic->is_active))>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
                <button class="btn btn-dark">Simpan</button>
            </form>
        </div>
    </div>
@endsection
