<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'category', 'description', 'duration_minutes',
        'price_matic', 'price_manual', 'price_sport', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_matic' => 'integer',
            'price_manual' => 'integer',
            'price_sport' => 'integer',
        ];
    }

    public function priceFor(string $vehicleType): ?int
    {
        return $this->{'price_'.$vehicleType} ?? null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableFor($query, string $vehicleType)
    {
        return $query->whereNotNull('price_'.$vehicleType);
    }

    public function lowestPrice(): ?int
    {
        return collect([$this->price_matic, $this->price_manual, $this->price_sport])->filter()->min();
    }
}
