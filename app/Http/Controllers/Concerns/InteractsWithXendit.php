<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

trait InteractsWithXendit
{
    protected function xendit(): PendingRequest
    {
        $key = config('services.xendit.secret_key');

        if (blank($key)) {
            throw new RuntimeException('XENDIT_SECRET_KEY belum diisi di file .env');
        }

        return Http::baseUrl(config('services.xendit.base_url'))
            ->withBasicAuth($key, '')
            ->withHeaders(['api-version' => config('services.xendit.api_version')])
            ->acceptJson()
            ->timeout(20);
    }

    protected function xenditCreateQris(string $referenceId, int $amount, string $description, \DateTimeInterface $expiresAt): array
    {
        $data = $this->xendit()->post('/v3/payment_requests', [
            'reference_id' => $referenceId,
            'type' => 'PAY',
            'country' => 'ID',
            'currency' => 'IDR',
            'request_amount' => $amount,
            'capture_method' => 'AUTOMATIC',
            'channel_code' => 'QRIS',
            'channel_properties' => [
                'expires_at' => \Carbon\CarbonImmutable::instance($expiresAt)->utc()->format('Y-m-d\TH:i:s\Z'),
            ],
            'description' => $description,
        ])->throw()->json();

        $qr = collect($data['actions'] ?? [])->firstWhere('descriptor', 'QR_STRING');

        if (! $qr) {
            throw new RuntimeException('Xendit tidak mengembalikan QR string.');
        }

        return [
            'id' => $data['payment_request_id'],
            'qr_string' => $qr['value'],
            'status' => $data['status'] ?? null,
            'payload' => $data,
        ];
    }

    protected function xenditGetPaymentRequest(string $paymentRequestId): array
    {
        return $this->xendit()->get('/v3/payment_requests/'.$paymentRequestId)->throw()->json();
    }

    protected function xenditSimulate(string $paymentRequestId, int $amount): array
    {
        return $this->xendit()
            ->post('/v3/payment_requests/'.$paymentRequestId.'/simulate', ['amount' => $amount])
            ->throw()
            ->json();
    }

    protected function mapXenditStatus(?string $status): ?string
    {
        return match ($status) {
            'SUCCEEDED' => 'paid',
            'EXPIRED', 'CANCELED' => 'expired',
            'FAILED' => 'failed',
            default => null,
        };
    }
}
