<?php

namespace App\Models;

use App\Traits\AccentInsensitiveSearch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory, HasUuids, AccentInsensitiveSearch;

    protected $fillable = [
        'name',
        'slug',
        'contact_name',
        'email',
        'phone',
    ];

    /**
     * Usar slug en lugar de id para las rutas.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot del modelo para generar slug automáticamente.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->slug)) {
                $supplier->slug = Str::slug($supplier->name);
                
                $count = 1;
                while (static::where('slug', $supplier->slug)->exists()) {
                    $supplier->slug = Str::slug($supplier->name) . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($supplier) {
            if ($supplier->isDirty('name')) {
                $supplier->slug = Str::slug($supplier->name);
                
                $count = 1;
                while (static::where('slug', $supplier->slug)->where('id', '!=', $supplier->id)->exists()) {
                    $supplier->slug = Str::slug($supplier->name) . '-' . $count;
                    $count++;
                }
            }
        });
    }

    // Relaciones
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
