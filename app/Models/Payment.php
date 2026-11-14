<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const TYPE_DP = 'dp';
    public const TYPE_FINAL = 'final';

    public const METHODS = [
        'qris' => 'QRIS',
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
    ];

    protected $fillable = [
        'booking_id', 'type', 'method', 'amount', 'received_amount', 'status',
        'reference_id', 'xendit_id', 'qr_string', 'expires_at', 'paid_at', 'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'received_amount' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->status === 'pending' && $this->expires_at && $this->expires_at->isPast());
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_DP ? 'DP Booking' : 'Pelunasan Service';
    }
}
