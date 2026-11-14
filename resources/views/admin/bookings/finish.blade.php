@extends('layouts.admin')

@section('title', 'Selesaikan Service')

@section('content')
    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-4">Selesaikan Service · {{ $booking->code }}</h4>

    <form method="POST" action="{{ route('admin.bookings.finish.store', $booking) }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-semibold py-3">Biaya Jasa Service</div>
                    <div class="card-body">
                        @foreach ($booking->items->where('is_additional', false) as $item)
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-md-8">{{ $item->name }}</div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="items[{{ $item->id }}][price]" value="{{ old('items.'.$item->id.'.price', $item->price) }}" min="0" class="form-control text-end price-input" data-qty="1" required>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <span class="fw-semibold">Biaya Tambahan <small class="text-secondary fw-normal">(sparepart, oli, dll)</small></span>
                        <button type="button" class="btn btn-sm btn-outline-dark" id="addExtra"><i class="bi bi-plus"></i> Tambah</button>
                    </div>
                    <div class="card-body" id="extras">
                        <p class="text-secondary small mb-0" id="emptyExtra">Belum ada biaya tambahan.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Total</span><span id="grandTotal" class="fw-semibold">Rp 0</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Sudah dibayar (DP)</span><span>- @rupiah($booking->paidAmount())</span></div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4"><span class="fw-semibold">Sisa Tagihan</span><span id="remaining" class="fs-5 fw-bold">Rp 0</span></div>
                        <button class="btn btn-success w-100" onclick="return confirm('Simpan dan terbitkan invoice?')">Terbitkan Invoice</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template id="extraTemplate">
        <div class="row g-2 align-items-center mb-2 extra-row">
            <div class="col-md-6"><input type="text" name="extras[__i__][name]" class="form-control" placeholder="Nama item" required></div>
            <div class="col-md-2"><input type="number" name="extras[__i__][qty]" value="1" min="1" class="form-control qty-input" required></div>
            <div class="col-md-3"><input type="number" name="extras[__i__][price]" min="0" class="form-control text-end price-input" placeholder="Harga" required></div>
            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-extra"><i class="bi bi-x"></i></button></div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        const paid = {{ $booking->paidAmount() }};
        const extras = document.getElementById('extras');
        const rupiah = value => 'Rp ' + Number(value).toLocaleString('id-ID');
        let index = 0;

        function recalc() {
            let total = 0;
            document.querySelectorAll('.price-input').forEach(input => {
                const row = input.closest('.extra-row');
                const qty = row ? Number(row.querySelector('.qty-input').value || 0) : 1;
                total += qty * Number(input.value || 0);
            });
            document.getElementById('grandTotal').textContent = rupiah(total);
            document.getElementById('remaining').textContent = rupiah(Math.max(0, total - paid));
            document.getElementById('emptyExtra').classList.toggle('d-none', extras.querySelector('.extra-row') !== null);
        }

        document.getElementById('addExtra').addEventListener('click', () => {
            const html = document.getElementById('extraTemplate').innerHTML.replaceAll('__i__', index++);
            extras.insertAdjacentHTML('beforeend', html);
            recalc();
        });

        extras.addEventListener('click', event => {
            if (event.target.closest('.remove-extra')) {
                event.target.closest('.extra-row').remove();
                recalc();
            }
        });

        document.addEventListener('input', recalc);
        recalc();
    </script>
@endpush
