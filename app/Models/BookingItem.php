<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = ['booking_id', 'service_id', 'name', 'qty', 'price', 'is_additional'];

    protected function casts(): array
    {
        return ['is_additional' => 'boolean', 'qty' => 'integer', 'price' => 'integer'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function subtotal(): int
    {
        return $this->qty * $this->price;
    }
}
