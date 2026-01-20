<?php

namespace App\Models;

use App\Traits\AccentInsensitiveSearch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes, AccentInsensitiveSearch;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'barcode',
        'sku',
        'description',
        'image_path',
        'cost_price',
        'sale_price',
        'tax_rate',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'track_stock',
    ];

    protected $casts = [
        'cost_price' => 'integer',
        'sale_price' => 'integer',
        'tax_rate' => 'integer',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
    ];

    // Relaciones
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('track_stock', true);
    }

    // Accessors para precios en formato decimal
    public function getCostPriceFormattedAttribute(): string
    {
        return number_format($this->cost_price / 100, 2);
    }

    public function getSalePriceFormattedAttribute(): string
    {
        return number_format($this->sale_price / 100, 2);
    }

    public function getPriceWithTaxAttribute(): float
    {
        return ($this->sale_price / 100) * (1 + $this->tax_rate / 100);
    }
}
