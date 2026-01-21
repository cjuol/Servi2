<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'date',
        'tax_base',
        'tax_rate_quantity',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
        'tax_base' => 'integer',
        'tax_rate_quantity' => 'integer',
    ];

    /**
     * Recalcula los totales del presupuesto sumando todas las líneas
     */
    public function recalculateTotals(): void
    {
        $this->tax_base = $this->details()->sum('tax_base');
        $this->tax_rate_quantity = $this->details()->sum('tax_rate_quantity');
        $this->saveQuietly();
    }

    /**
     * Calcula el total del presupuesto (base + impuestos)
     */
    public function getTotalAttribute(): int
    {
        return $this->tax_base + $this->tax_rate_quantity;
    }

    // Relaciones
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BudgetDetail::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }
}
