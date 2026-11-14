@extends('layouts.admin')

@section('title', 'Mekanik')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Mekanik</h4>
        <a href="{{ route('admin.mechanics.create') }}" class="btn btn-dark"><i class="bi bi-plus"></i> Tambah</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>No. HP</th><th>Spesialisasi</th><th>Sedang Dikerjakan</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($mechanics as $mechanic)
                        <tr>
                            <td class="fw-semibold">{{ $mechanic->name }}</td>
                            <td>{{ $mechanic->phone ?? '-' }}</td>
                            <td>{{ $mechanic->specialty ?? '-' }}</td>
                            <td>{{ $mechanic->active_jobs }} motor</td>
                            <td><span class="badge text-bg-{{ $mechanic->is_active ? 'success' : 'secondary' }}">{{ $mechanic->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.mechanics.edit', $mechanic) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                <form method="POST" action="{{ route('admin.mechanics.destroy', $mechanic) }}" class="d-inline" onsubmit="return confirm('Hapus mekanik ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada mekanik.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
