<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_code',
        'customer_id',
        'driver_id',
        'delivery_date',
        'delivery_time',
        'quantity_delivered',
        'empty_cylinders_collected',
        'status',
        'special_instructions',
        'driver_notes',
        'delivery_photos',
        'arrived_at',
        'arrival_latitude',
        'arrival_longitude',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'delivery_time' => 'datetime:H:i',
            'delivery_photos' => 'array',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
            'arrival_latitude' => 'float',
            'arrival_longitude' => 'float',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(DeliveryLog::class);
    }

    public function getQuantityOrderedAttribute(): int
    {
        return $this->items->sum('quantity');
    }
}