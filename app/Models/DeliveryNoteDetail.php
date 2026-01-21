<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNoteDetail extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'delivery_note_id',
        'product_id',
        'quantity',
        'tax_base',
        'tax_rate_quantity',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'tax_base' => 'integer',
        'tax_rate_quantity' => 'integer',
        'total' => 'integer',
    ];

    /**
     * Boot method: Recalcular totales automáticamente
     */
    protected static function booted(): void
    {
        // Calcular total automáticamente antes de guardar
        static::saving(function (DeliveryNoteDetail $detail) {
            $detail->total = $detail->tax_base + $detail->tax_rate_quantity;
        });

        // Recalcular totales del albarán después de crear
        static::created(function (DeliveryNoteDetail $detail) {
            $detail->deliveryNote->recalculateTotals();
        });

        // Recalcular totales del albarán después de actualizar
        static::updated(function (DeliveryNoteDetail $detail) {
            $detail->deliveryNote->recalculateTotals();
        });

        // Recalcular totales del albarán después de eliminar
        static::deleted(function (DeliveryNoteDetail $detail) {
            $detail->deliveryNote->recalculateTotals();
        });
    }

    // Relaciones
    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
