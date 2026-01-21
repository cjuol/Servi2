<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'budget_id',
        'invoice_id',
        'date',
        'tax_base',
        'tax_rate_quantity',
        'stored',
    ];

    protected $casts = [
        'date' => 'datetime',
        'tax_base' => 'integer',
        'tax_rate_quantity' => 'integer',
        'stored' => 'boolean',
    ];

    /**
     * Recalcula los totales del albarán sumando todas las líneas
     */
    public function recalculateTotals(): void
    {
        $this->tax_base = $this->details()->sum('tax_base');
        $this->tax_rate_quantity = $this->details()->sum('tax_rate_quantity');
        $this->saveQuietly();
    }

    /**
     * Calcula el total del albarán (base + impuestos)
     */
    public function getTotalAttribute(): int
    {
        return $this->tax_base + $this->tax_rate_quantity;
    }

    /**
     * Marca el albarán como almacenado y crea los movimientos de stock
     */
    public function storeInInventory(?User $user = null): void
    {
        if ($this->stored) {
            return; // Ya está almacenado
        }

        foreach ($this->details as $detail) {
            // Crear movimiento de stock
            StockMovement::create([
                'product_id' => $detail->product_id,
                'user_id' => $user?->id,
                'delivery_note_id' => $this->id,
                'quantity' => $detail->quantity, // Positivo = entrada
                'type' => StockMovement::TYPE_PURCHASE,
                'reason' => "Albarán #{$this->id}",
            ]);

            // Actualizar stock del producto
            $detail->product->increment('stock_quantity', $detail->quantity);
        }

        $this->update(['stored' => true]);
    }

    // Relaciones
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryNoteDetail::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeStored($query)
    {
        return $query->where('stored', true);
    }

    public function scopePending($query)
    {
        return $query->where('stored', false);
    }
}
