<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesPayments;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    use HandlesPayments;

    public function __invoke(Request $request)
    {
        $token = (string) config('services.xendit.webhook_token');

        if ($token === '' || ! hash_equals($token, (string) $request->header('x-callback-token'))) {
            return response()->json(['message' => 'Invalid token'], 403);
        }

        $data = $request->input('data', []);

        $payment = Payment::query()
            ->when($data['payment_request_id'] ?? null, fn ($q, $id) => $q->where('xendit_id', $id))
            ->when(! isset($data['payment_request_id']), fn ($q) => $q->where('reference_id', $data['reference_id'] ?? ''))
            ->first();

        if (! $payment) {
            Log::info('Webhook Xendit tanpa payment terkait.', ['event' => $request->input('event')]);

            return response()->json(['message' => 'ignored']);
        }

        if ($request->input('event') === 'payment.capture' && ($data['status'] ?? null) === 'SUCCEEDED') {
            if ((int) ($data['request_amount'] ?? $payment->amount) >= $payment->amount) {
                $this->markPaymentPaid($payment, $request->all());
            }
        } elseif ($request->input('event') === 'payment.failure' && $payment->status === 'pending') {
            $payment->update(['status' => 'failed', 'payload' => $request->all()]);
        }

        return response()->json(['message' => 'ok']);
    }
}
