<?php

namespace App\Http\Controllers\Concerns;

use App\Mail\BookingCompletedMail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

trait HandlesPayments
{
    use InteractsWithXendit;

    protected function createQrisPayment(Booking $booking, string $type, int $amount): Payment
    {
        $expiresAt = now()->addMinutes(config('bengkel.payment_expiry_minutes'));
        $referenceId = strtoupper($type).'-'.$booking->code.'-'.Str::upper(Str::random(5));
        $description = ($type === Payment::TYPE_DP ? 'DP Booking ' : 'Pelunasan Service ').$booking->code;

        $result = $this->xenditCreateQris($referenceId, $amount, $description, $expiresAt);

        return $booking->payments()->create([
            'type' => $type,
            'method' => 'qris',
            'amount' => $amount,
            'status' => 'pending',
            'reference_id' => $referenceId,
            'xendit_id' => $result['id'],
            'qr_string' => $result['qr_string'],
            'expires_at' => $expiresAt,
            'payload' => $result['payload'],
        ]);
    }

    protected function syncQrisPayment(Payment $payment): Payment
    {
        if ($payment->status !== 'pending' || ! $payment->xendit_id) {
            return $payment;
        }

        try {
            $data = $this->xenditGetPaymentRequest($payment->xendit_id);
            $status = $this->mapXenditStatus($data['status'] ?? null);

            if ($status === 'paid') {
                $this->markPaymentPaid($payment, $data);
            } elseif ($status) {
                $payment->update(['status' => $status]);
            }
        } catch (Throwable $e) {
            Log::warning('Gagal sinkron status Xendit: '.$e->getMessage(), ['payment' => $payment->id]);
        }

        if ($payment->status === 'pending' && $payment->expires_at?->isPast()) {
            $payment->update(['status' => 'expired']);
        }

        return $payment->refresh();
    }

    protected function markPaymentPaid(Payment $payment, array $payload = [], array $attributes = []): bool
    {
        $updated = DB::transaction(function () use ($payment, $payload, $attributes) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if ($locked->isPaid()) {
                return false;
            }

            $locked->update(array_merge([
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => $payload ?: $locked->payload,
            ], $attributes));

            $booking = $locked->booking;

            if ($locked->type === Payment::TYPE_DP) {
                if (in_array($booking->status, ['pending_payment', 'expired'])) {
                    $booking->update(['status' => 'confirmed']);
                }
            } else {
                $booking->update(['status' => 'completed', 'completed_at' => now()]);
            }

            return true;
        });

        if ($updated) {
            $payment->refresh();
            $this->sendPaymentNotification($payment);
        }

        return $updated;
    }

    protected function sendPaymentNotification(Payment $payment): bool
    {
        $booking = $payment->booking->load(['items', 'payments', 'mechanic']);

        $mail = $payment->type === Payment::TYPE_DP
            ? new BookingConfirmedMail($booking, $payment, $this->renderInvoicePdf($booking))
            : new BookingCompletedMail($booking, $payment, $this->renderInvoicePdf($booking), $this->renderReceiptPdf($booking, $payment));

        try {
            Mail::to($booking->customer_email, $booking->customer_name)->send($mail);

            return true;
        } catch (Throwable $e) {
            Log::error('Gagal mengirim email notifikasi: '.$e->getMessage(), ['booking' => $booking->code]);

            return false;
        }
    }

    protected function renderInvoicePdf(Booking $booking): string
    {
        $booking->loadMissing(['items', 'payments', 'mechanic']);

        return Pdf::loadView('pdf.invoice', ['booking' => $booking])->setPaper('a4')->output();
    }

    protected function renderReceiptPdf(Booking $booking, Payment $payment): string
    {
        $booking->loadMissing(['items', 'payments']);

        return Pdf::loadView('pdf.receipt', ['booking' => $booking, 'payment' => $payment])
            ->setPaper([0, 0, 226.77, 650])
            ->output();
    }
}
