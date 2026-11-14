<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesPayments;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Mechanic;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class BookingController extends Controller
{
    use HandlesPayments;

    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->when($request->date, fn ($q, $date) => $q->whereDate('booking_date', $date))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->q, function ($q, $search) {
                $q->where(fn ($q) => $q->where('code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%"));
            })
            ->latest('booking_date')
            ->orderBy('time_slot')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statuses' => Booking::STATUSES,
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['items', 'payments' => fn ($q) => $q->latest(), 'mechanic']);
        $mechanics = Mechanic::where('is_active', true)->orderBy('name')->get();

        return view('admin.bookings.show', compact('booking', 'mechanics'));
    }

    public function checkIn(Request $request, Booking $booking)
    {
        abort_unless($booking->status === 'confirmed', 422);

        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:15'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'plate_number' => strtoupper($data['plate_number']),
            'admin_notes' => $data['admin_notes'] ?? null,
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return back()->with('success', 'Customer sudah diverifikasi dan check-in.');
    }

    public function startService(Request $request, Booking $booking)
    {
        abort_unless($booking->status === 'checked_in', 422);

        $data = $request->validate([
            'mechanic_id' => ['required', Rule::exists('mechanics', 'id')->where('is_active', true)],
        ]);

        $booking->update([
            'mechanic_id' => $data['mechanic_id'],
            'status' => 'in_service',
            'service_started_at' => now(),
        ]);

        return back()->with('success', 'Mekanik ditugaskan, service dimulai.');
    }

    public function cancel(Booking $booking)
    {
        abort_unless(in_array($booking->status, ['pending_payment', 'confirmed']), 422);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking dibatalkan.');
    }

    public function finishForm(Booking $booking)
    {
        abort_unless($booking->status === 'in_service', 422);

        return view('admin.bookings.finish', ['booking' => $booking->load('items')]);
    }

    public function finish(Request $request, Booking $booking)
    {
        abort_unless($booking->status === 'in_service', 422);

        $data = $request->validate([
            'items' => ['array'],
            'items.*.price' => ['required', 'integer', 'min:0'],
            'extras' => ['nullable', 'array'],
            'extras.*.name' => ['required', 'string', 'max:100'],
            'extras.*.qty' => ['required', 'integer', 'min:1'],
            'extras.*.price' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($booking, $data) {
            foreach ($booking->items()->where('is_additional', false)->get() as $item) {
                if (isset($data['items'][$item->id])) {
                    $item->update(['price' => $data['items'][$item->id]['price']]);
                }
            }

            $booking->items()->where('is_additional', true)->delete();

            foreach ($data['extras'] ?? [] as $extra) {
                $booking->items()->create($extra + ['is_additional' => true]);
            }

            $booking->load('items');
            $booking->update([
                'total_amount' => $booking->itemsTotal(),
                'status' => 'waiting_payment',
                'service_finished_at' => now(),
            ]);
        });

        $booking->load('payments');

        if ($booking->remainingAmount() === 0) {
            $payment = $booking->payments()->create([
                'type' => Payment::TYPE_FINAL,
                'method' => 'cash',
                'amount' => 0,
                'reference_id' => 'FINAL-'.$booking->code,
            ]);
            $this->markPaymentPaid($payment);

            return redirect()->route('admin.bookings.show', $booking)->with('success', 'Service selesai, tidak ada sisa tagihan.');
        }

        return redirect()->route('admin.bookings.payment', $booking);
    }

    public function payment(Booking $booking)
    {
        if ($booking->status !== 'waiting_payment') {
            return redirect()->route('admin.bookings.show', $booking);
        }

        $booking->load(['items', 'payments']);
        $payment = $booking->finalPayment;

        if ($payment && $payment->method === 'qris') {
            $payment = $this->syncQrisPayment($payment);

            if ($payment->isPaid()) {
                return redirect()->route('admin.bookings.show', $booking)->with('success', 'Pelunasan QRIS diterima.');
            }
        }

        return view('admin.bookings.payment', [
            'booking' => $booking,
            'payment' => $payment && $payment->method === 'qris' && $payment->status === 'pending' && ! $payment->isExpired() ? $payment : null,
        ]);
    }

    public function payQris(Booking $booking)
    {
        abort_unless($booking->status === 'waiting_payment', 422);

        $booking->payments()->where('type', Payment::TYPE_FINAL)->where('status', 'pending')->update(['status' => 'expired']);

        try {
            $this->createQrisPayment($booking, Payment::TYPE_FINAL, $booking->load('payments')->remainingAmount());
        } catch (Throwable $e) {
            Log::error('Gagal membuat QRIS pelunasan: '.$e->getMessage(), ['booking' => $booking->code]);

            return back()->with('error', 'QRIS gagal dibuat: '.$e->getMessage());
        }

        return redirect()->route('admin.bookings.payment', $booking);
    }

    public function payManual(Request $request, Booking $booking)
    {
        abort_unless($booking->status === 'waiting_payment', 422);

        $remaining = $booking->load('payments')->remainingAmount();

        $data = $request->validate([
            'method' => ['required', Rule::in(['cash', 'transfer'])],
            'received_amount' => ['nullable', 'required_if:method,cash', 'integer', 'min:'.$remaining],
        ]);

        $booking->payments()->where('type', Payment::TYPE_FINAL)->where('status', 'pending')->update(['status' => 'expired']);

        $payment = $booking->payments()->create([
            'type' => Payment::TYPE_FINAL,
            'method' => $data['method'],
            'amount' => $remaining,
            'reference_id' => 'FINAL-'.$booking->code.'-'.now()->format('His'),
        ]);

        $this->markPaymentPaid($payment, [], [
            'received_amount' => $data['method'] === 'cash' ? $data['received_amount'] : $remaining,
        ]);

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Pelunasan dicatat, invoice & struk dikirim ke email customer.');
    }

    public function paymentStatus(Booking $booking)
    {
        $payment = $booking->finalPayment;

        if ($payment && $payment->method === 'qris') {
            $payment = $this->syncQrisPayment($payment);
        }

        return response()->json([
            'payment_status' => $payment?->isExpired() ? 'expired' : $payment?->status,
            'redirect' => route('admin.bookings.show', $booking),
        ]);
    }

    public function simulate(Booking $booking)
    {
        abort_unless(config('services.xendit.test_mode'), 404);

        $payment = $booking->finalPayment;
        abort_unless($payment && $payment->status === 'pending' && $payment->xendit_id, 404);

        try {
            $this->xenditSimulate($payment->xendit_id, $payment->amount);
        } catch (Throwable $e) {
            return back()->with('error', 'Simulasi gagal: '.$e->getMessage());
        }

        return back()->with('success', 'Simulasi pembayaran dikirim.');
    }

    public function resendNotification(Booking $booking)
    {
        $payment = $booking->status === 'completed' ? $booking->finalPayment : $booking->dpPayment;
        abort_unless($payment && $payment->isPaid(), 422);

        return $this->sendPaymentNotification($payment)
            ? back()->with('success', 'Email notifikasi dikirim ulang ke '.$booking->customer_email.'.')
            : back()->with('error', 'Email gagal dikirim, cek konfigurasi MAIL di .env dan storage/logs/laravel.log.');
    }
}
