<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesPayments;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingController extends Controller
{
    use HandlesPayments;

    public function create(Request $request)
    {
        $services = Service::active()->orderBy('category')->orderBy('id')->get();
        $selected = array_map('intval', (array) $request->query('service', []));

        return view('booking.create', [
            'services' => $services,
            'selected' => old('services', $selected),
            'types' => config('bengkel.vehicle_types'),
            'slots' => config('bengkel.time_slots'),
        ]);
    }

    public function slots(Request $request)
    {
        $request->validate(['date' => ['required', 'date']]);

        return response()->json($this->slotAvailability(Carbon::parse($request->date)));
    }

    public function store(Request $request)
    {
        $types = array_keys(config('bengkel.vehicle_types'));

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_email' => ['required', 'email', 'max:100'],
            'vehicle_type' => ['required', Rule::in($types)],
            'vehicle_brand' => ['required', 'string', 'max:50'],
            'vehicle_model' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:15'],
            'booking_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(30)->toDateString()],
            'time_slot' => ['required', Rule::in(config('bengkel.time_slots'))],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer', 'exists:services,id'],
            'complaint' => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($data['booking_date']);
        $slot = collect($this->slotAvailability($date))->firstWhere('slot', $data['time_slot']);

        if (! $slot || ! $slot['available']) {
            throw ValidationException::withMessages(['time_slot' => 'Slot waktu ini sudah penuh atau tidak tersedia.']);
        }

        $services = Service::active()->whereIn('id', $data['services'])->get()
            ->filter(fn ($service) => $service->priceFor($data['vehicle_type']) !== null);

        if ($services->isEmpty()) {
            throw ValidationException::withMessages(['services' => 'Layanan yang dipilih tidak tersedia untuk jenis motor ini.']);
        }

        $booking = DB::transaction(function () use ($data, $services) {
            $taken = Booking::occupying()
                ->whereDate('booking_date', $data['booking_date'])
                ->where('time_slot', $data['time_slot'])
                ->lockForUpdate()
                ->count();

            if ($taken >= config('bengkel.slot_capacity')) {
                throw ValidationException::withMessages(['time_slot' => 'Slot waktu ini baru saja penuh, silakan pilih slot lain.']);
            }

            $total = $services->sum(fn ($service) => $service->priceFor($data['vehicle_type']));

            $booking = Booking::create([
                'code' => Booking::generateCode(),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'vehicle_type' => $data['vehicle_type'],
                'vehicle_brand' => $data['vehicle_brand'],
                'vehicle_model' => $data['vehicle_model'],
                'plate_number' => strtoupper($data['plate_number']),
                'booking_date' => $data['booking_date'],
                'time_slot' => $data['time_slot'],
                'complaint' => $data['complaint'] ?? null,
                'status' => 'pending_payment',
                'estimated_total' => $total,
                'total_amount' => $total,
                'dp_amount' => (int) ceil($total * config('bengkel.dp_percent') / 100),
            ]);

            foreach ($services as $service) {
                $booking->items()->create([
                    'service_id' => $service->id,
                    'name' => $service->name,
                    'qty' => 1,
                    'price' => $service->priceFor($data['vehicle_type']),
                ]);
            }

            return $booking;
        });

        try {
            $this->createQrisPayment($booking, Payment::TYPE_DP, $booking->dp_amount);
        } catch (Throwable $e) {
            Log::error('Gagal membuat QRIS DP: '.$e->getMessage(), ['booking' => $booking->code]);

            return redirect()->route('booking.pay', $booking)
                ->with('error', 'QRIS belum berhasil dibuat. Silakan coba buat ulang QR.');
        }

        return redirect()->route('booking.pay', $booking);
    }

    public function pay(Booking $booking)
    {
        if ($booking->status !== 'pending_payment') {
            return redirect()->route('booking.show', $booking);
        }

        $payment = $booking->dpPayment;

        if ($payment) {
            $payment = $this->syncQrisPayment($payment);

            if ($payment->isPaid()) {
                return redirect()->route('booking.show', $booking)->with('success', 'Pembayaran DP berhasil.');
            }
        }

        return view('booking.pay', [
            'booking' => $booking->load('items'),
            'payment' => $payment && ! $payment->isExpired() ? $payment : null,
        ]);
    }

    public function regenerateQris(Booking $booking)
    {
        abort_unless($booking->status === 'pending_payment', 404);

        $taken = Booking::occupying()
            ->whereKeyNot($booking->id)
            ->whereDate('booking_date', $booking->booking_date)
            ->where('time_slot', $booking->time_slot)
            ->count();

        if ($taken >= config('bengkel.slot_capacity') || $booking->booking_date->lt(today())) {
            $booking->update(['status' => 'expired']);

            return redirect()->route('booking.show', $booking)->with('error', 'Slot sudah tidak tersedia, silakan buat booking baru.');
        }

        try {
            $booking->payments()->where('type', Payment::TYPE_DP)->where('status', 'pending')->update(['status' => 'expired']);
            $this->createQrisPayment($booking, Payment::TYPE_DP, $booking->dp_amount);
            $booking->touch();
        } catch (Throwable $e) {
            Log::error('Gagal membuat ulang QRIS DP: '.$e->getMessage(), ['booking' => $booking->code]);

            return back()->with('error', 'QRIS gagal dibuat, coba beberapa saat lagi.');
        }

        return redirect()->route('booking.pay', $booking);
    }

    public function status(Booking $booking)
    {
        $payment = $booking->dpPayment ? $this->syncQrisPayment($booking->dpPayment) : null;

        return response()->json([
            'payment_status' => $payment?->isExpired() ? 'expired' : $payment?->status,
            'booking_status' => $booking->refresh()->status,
            'redirect' => route('booking.show', $booking),
        ]);
    }

    public function simulate(Booking $booking)
    {
        abort_unless(config('services.xendit.test_mode'), 404);

        $payment = $booking->dpPayment;
        abort_unless($payment && $payment->status === 'pending' && $payment->xendit_id, 404);

        try {
            $this->xenditSimulate($payment->xendit_id, $payment->amount);
        } catch (Throwable $e) {
            return back()->with('error', 'Simulasi gagal: '.$e->getMessage());
        }

        return back()->with('success', 'Simulasi pembayaran dikirim. Status akan diperbarui otomatis.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['items', 'payments' => fn ($q) => $q->latest(), 'mechanic']);

        return view('booking.show', compact('booking'));
    }

    public function invoice(Booking $booking)
    {
        abort_if($booking->paidAmount() === 0, 404);

        return response($this->renderInvoicePdf($booking), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Invoice-'.$booking->code.'.pdf"',
        ]);
    }

    public function receipt(Booking $booking)
    {
        $payment = $booking->finalPayment;
        abort_unless($payment && $payment->isPaid(), 404);

        return response($this->renderReceiptPdf($booking, $payment), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Struk-'.$booking->code.'.pdf"',
        ]);
    }

    protected function slotAvailability(Carbon $date): array
    {
        if (in_array($date->dayOfWeek, config('bengkel.closed_days')) || $date->isBefore(today())) {
            return collect(config('bengkel.time_slots'))
                ->map(fn ($slot) => ['slot' => $slot, 'remaining' => 0, 'available' => false, 'reason' => 'closed'])
                ->all();
        }

        $taken = Booking::occupying()
            ->whereDate('booking_date', $date)
            ->selectRaw('time_slot, count(*) as total')
            ->groupBy('time_slot')
            ->pluck('total', 'time_slot');

        return collect(config('bengkel.time_slots'))->map(function ($slot) use ($date, $taken) {
            $cutoff = $date->copy()
                ->setTimeFromTimeString(explode('-', $slot)[1])
                ->subMinutes(config('bengkel.slot_cutoff_minutes'));
            $remaining = max(0, config('bengkel.slot_capacity') - ($taken[$slot] ?? 0));
            $passed = $cutoff->isPast();

            return [
                'slot' => $slot,
                'remaining' => $remaining,
                'reason' => $passed ? 'passed' : ($remaining === 0 ? 'full' : null),
                'available' => $remaining > 0 && ! $passed,
            ];
        })->all();
    }
}
