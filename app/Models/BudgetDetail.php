<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDetail extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'budget_id',
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
        static::saving(function (BudgetDetail $detail) {
            $detail->total = $detail->tax_base + $detail->tax_rate_quantity;
        });

        // Recalcular totales del presupuesto después de crear
        static::created(function (BudgetDetail $detail) {
            $detail->budget->recalculateTotals();
        });

        // Recalcular totales del presupuesto después de actualizar
        static::updated(function (BudgetDetail $detail) {
            $detail->budget->recalculateTotals();
        });

        // Recalcular totales del presupuesto después de eliminar
        static::deleted(function (BudgetDetail $detail) {
            $detail->budget->recalculateTotals();
        });
    }

    // Relaciones
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
