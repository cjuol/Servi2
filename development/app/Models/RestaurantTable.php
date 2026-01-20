<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'capacity',
        'is_available',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_available' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene la orden activa (abierta) de esta mesa
     */
    public function currentOrder(): ?Order
    {
        return $this->orders()
            ->where('status', OrderStatus::OPEN->value)
            ->first();
    }
}
