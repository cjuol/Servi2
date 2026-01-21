<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'user_id',
        'delivery_note_id',
        'quantity',
        'type',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Tipos de movimientos
    public const TYPE_SALE = 'sale';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_WASTE = 'waste';

    // Relaciones
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForProduct($query, string $productId)
    {
        return $query->where('product_id', $productId);
    }
}
