<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUSES = [
        'pending_payment' => ['Menunggu DP', 'secondary'],
        'confirmed' => ['Terkonfirmasi', 'primary'],
        'checked_in' => ['Sudah Datang', 'info'],
        'in_service' => ['Dikerjakan', 'warning'],
        'waiting_payment' => ['Menunggu Pelunasan', 'danger'],
        'completed' => ['Selesai', 'success'],
        'expired' => ['Kedaluwarsa', 'dark'],
        'cancelled' => ['Dibatalkan', 'dark'],
    ];

    protected $fillable = [
        'code', 'customer_name', 'customer_email',
        'vehicle_type', 'vehicle_brand', 'vehicle_model', 'plate_number',
        'booking_date', 'time_slot', 'complaint', 'mechanic_id', 'status',
        'estimated_total', 'dp_amount', 'total_amount', 'admin_notes',
        'checked_in_at', 'service_started_at', 'service_finished_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'estimated_total' => 'integer',
            'dp_amount' => 'integer',
            'total_amount' => 'integer',
            'checked_in_at' => 'datetime',
            'service_started_at' => 'datetime',
            'service_finished_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function generateCode(): string
    {
        do {
            $code = 'BK'.now()->format('ymd').strtoupper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function dpPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('type', Payment::TYPE_DP)->latestOfMany();
    }

    public function finalPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('type', Payment::TYPE_FINAL)->latestOfMany();
    }

    public function scopeOccupying($query)
    {
        return $query->whereNotIn('status', ['expired', 'cancelled'])
            ->where(function ($q) {
                $q->where('status', '!=', 'pending_payment')
                    ->orWhere('updated_at', '>=', now()->subMinutes(config('bengkel.payment_expiry_minutes')));
            });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }

    public function vehicleTypeLabel(): string
    {
        return config('bengkel.vehicle_types')[$this->vehicle_type] ?? $this->vehicle_type;
    }

    public function itemsTotal(): int
    {
        return $this->items->sum(fn ($item) => $item->subtotal());
    }

    public function paidAmount(): int
    {
        return (int) $this->payments->where('status', 'paid')->sum('amount');
    }

    public function remainingAmount(): int
    {
        return max(0, $this->total_amount - $this->paidAmount());
    }
}
