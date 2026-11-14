@extends('layouts.app')

@section('title', 'Booking Service')

@section('content')
    <div class="container py-5">
        <h1 class="fw-bold mb-1">Booking Service</h1>
        <p class="text-secondary mb-4">Isi data di bawah, lalu bayar DP {{ config('bengkel.dp_percent') }}% via QRIS untuk mengunci jadwal.</p>

        @if ($errors->any())
            <div class="alert alert-danger">Periksa kembali isian yang ditandai merah.</div>
        @endif

        <form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
            @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">1. Data Customer</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-control @error('customer_name') is-invalid @enderror" required>
                                    @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="form-control @error('customer_email') is-invalid @enderror" placeholder="nama@email.com" required>
                                    @error('customer_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Invoice dan struk dikirim ke email ini.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">2. Data Motor</h5>
                            <div class="mb-3">
                                <label class="form-label d-block">Jenis Motor</label>
                                <div class="btn-group w-100" role="group">
                                    @foreach ($types as $key => $label)
                                        <input type="radio" class="btn-check" name="vehicle_type" id="type_{{ $key }}" value="{{ $key }}" @checked(old('vehicle_type', 'matic') === $key)>
                                        <label class="btn btn-outline-dark" for="type_{{ $key }}">{{ $label }}</label>
                                    @endforeach
                                </div>
                                @error('vehicle_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Merek</label>
                                    <input type="text" name="vehicle_brand" value="{{ old('vehicle_brand') }}" class="form-control @error('vehicle_brand') is-invalid @enderror" placeholder="Honda" required>
                                    @error('vehicle_brand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipe</label>
                                    <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" class="form-control @error('vehicle_model') is-invalid @enderror" placeholder="Vario 125" required>
                                    @error('vehicle_model') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Plat Nomor</label>
                                    <input type="text" name="plate_number" value="{{ old('plate_number') }}" class="form-control text-uppercase @error('plate_number') is-invalid @enderror" placeholder="D 1234 ABC" required>
                                    @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">3. Pilih Layanan</h5>
                            @error('services') <div class="alert alert-danger py-2 small">{{ $message }}</div> @enderror
                            <div class="list-group">
                                @foreach ($services as $service)
                                    <label class="list-group-item d-flex gap-3 align-items-center service-option"
                                           data-price-matic="{{ $service->price_matic }}"
                                           data-price-manual="{{ $service->price_manual }}"
                                           data-price-sport="{{ $service->price_sport }}">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="services[]" value="{{ $service->id }}" @checked(in_array($service->id, $selected))>
                                        <span class="flex-grow-1">
                                            <span class="fw-semibold">{{ $service->name }}</span>
                                            <small class="d-block text-secondary">{{ $service->category }} · ± {{ $service->duration_minutes }} menit</small>
                                        </span>
                                        <span class="fw-semibold text-nowrap service-price"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">4. Jadwal Kedatangan</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="booking_date" id="bookingDate" value="{{ old('booking_date') }}" min="{{ today()->toDateString() }}" max="{{ today()->addDays(30)->toDateString() }}" class="form-control @error('booking_date') is-invalid @enderror" required>
                                    @error('booking_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">{{ config('bengkel.open_hours') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jam</label>
                                    <select name="time_slot" id="timeSlot" class="form-select @error('time_slot') is-invalid @enderror" data-old="{{ old('time_slot') }}" required>
                                        <option value="">Pilih tanggal dulu</option>
                                    </select>
                                    @error('time_slot') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keluhan <span class="text-secondary small">(opsional)</span></label>
                                    <textarea name="complaint" rows="3" class="form-control" placeholder="Contoh: tarikan berat, rem bunyi">{{ old('complaint') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="fw-semibold mb-3">Ringkasan</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-secondary">Estimasi Biaya</span>
                                <span id="estimateTotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-secondary">DP {{ config('bengkel.dp_percent') }}%</span>
                                <span class="fw-bold fs-5" id="dpTotal">Rp 0</span>
                            </div>
                            <p class="small text-secondary">Sisa biaya + sparepart dibayar setelah service selesai. DP tidak dapat dikembalikan jika tidak datang.</p>
                            <button type="submit" class="btn btn-warning w-100 fw-semibold">Lanjut Bayar DP <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const dpPercent = {{ config('bengkel.dp_percent') }};
        const slotsUrl = @json(route('booking.slots'));
        const rupiah = value => 'Rp ' + Number(value).toLocaleString('id-ID');
        const options = document.querySelectorAll('.service-option');
        const dateInput = document.getElementById('bookingDate');
        const slotSelect = document.getElementById('timeSlot');

        function currentType() {
            return document.querySelector('input[name=vehicle_type]:checked').value;
        }

        function refreshServices() {
            let total = 0;
            options.forEach(option => {
                const checkbox = option.querySelector('input');
                const price = option.dataset['price' + currentType().charAt(0).toUpperCase() + currentType().slice(1)];
                const available = price !== '';
                checkbox.disabled = !available;
                if (!available) checkbox.checked = false;
                option.classList.toggle('text-secondary', !available);
                option.querySelector('.service-price').textContent = available ? rupiah(price) : 'Tidak tersedia';
                if (checkbox.checked) total += Number(price);
            });
            document.getElementById('estimateTotal').textContent = rupiah(total);
            document.getElementById('dpTotal').textContent = rupiah(Math.ceil(total * dpPercent / 100));
        }

        async function refreshSlots() {
            if (!dateInput.value) return;
            slotSelect.innerHTML = '<option value="">Memuat...</option>';
            const response = await fetch(slotsUrl + '?date=' + dateInput.value);
            const slots = await response.json();
            const hasAvailable = slots.some(slot => slot.available);
            slotSelect.innerHTML = '<option value="">' + (hasAvailable ? 'Pilih jam' : 'Tidak ada slot tersedia') + '</option>';
            slots.forEach(slot => {
                const labels = { passed: ' (sudah lewat)', full: ' (penuh)', closed: ' (tutup)' };
                const option = new Option(slot.slot + (slot.available ? ` (sisa ${slot.remaining})` : labels[slot.reason]), slot.slot);
                option.disabled = !slot.available;
                option.selected = slot.slot === slotSelect.dataset.old && slot.available;
                slotSelect.add(option);
            });
        }

        document.querySelectorAll('input[name=vehicle_type], .service-option input').forEach(el => el.addEventListener('change', refreshServices));
        dateInput.addEventListener('change', refreshSlots);
        refreshServices();
        refreshSlots();
    </script>
@endpush
