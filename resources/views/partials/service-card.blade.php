<div class="card h-100 border-0 shadow-sm">
    <div class="card-body d-flex flex-column">
        <span class="badge text-bg-light border align-self-start mb-2">{{ $service->category }}</span>
        <h5 class="card-title fw-semibold">{{ $service->name }}</h5>
        <p class="card-text text-secondary small flex-grow-1">{{ $service->description }}</p>
        <ul class="list-group list-group-flush small mb-3">
            @foreach (config('bengkel.vehicle_types') as $key => $label)
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span>{{ $label }}</span>
                    @if ($service->priceFor($key) !== null)
                        <span class="fw-semibold">@rupiah($service->priceFor($key))</span>
                    @else
                        <span class="text-secondary">-</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-secondary"><i class="bi bi-clock"></i> ± {{ $service->duration_minutes }} menit</small>
            <a href="{{ route('booking.create', ['service' => [$service->id]]) }}" class="btn btn-sm btn-dark">Booking</a>
        </div>
    </div>
</div>
