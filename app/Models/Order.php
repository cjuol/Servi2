<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'restaurant_table_id',
        'user_id',
        'status',
        'total',
        'notes',
    ];

    protected $casts = [
        'total' => 'integer',
        'status' => OrderStatus::class,
    ];

    /**
     * Recalcula el total del pedido sumando todos los subtotales de las líneas
     */
    public function recalculateTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->saveQuietly(); // Guardar sin disparar eventos
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
