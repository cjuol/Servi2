<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'tax_rate' => 'integer',
        'subtotal' => 'integer',
    ];

    /**
     * Boot method: Implementa snapshot logic y recálculo automático
     */
    protected static function booted(): void
    {
        // Snapshot Logic: Copiar datos del producto al crear la línea
        static::creating(function (OrderItem $item) {
            // Si se proporciona product_id y no se han establecido manualmente los campos
            if ($item->product_id && !$item->isDirty('unit_price')) {
                $product = Product::find($item->product_id);
                
                if ($product) {
                    // Copiar datos del producto para inmutabilidad histórica
                    $item->unit_price = $product->sale_price;
                    $item->tax_rate = $product->tax_rate;
                }
            }
        });

        // Calcular subtotal automáticamente antes de guardar
        static::saving(function (OrderItem $item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });

        // Recalcular total del pedido después de crear
        static::created(function (OrderItem $item) {
            $item->order->recalculateTotal();
        });

        // Recalcular total del pedido después de actualizar
        static::updated(function (OrderItem $item) {
            $item->order->recalculateTotal();
        });

        // Recalcular total del pedido después de eliminar
        static::deleted(function (OrderItem $item) {
            $item->order->recalculateTotal();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
