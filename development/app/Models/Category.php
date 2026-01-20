<?php

namespace App\Models;

use App\Traits\AccentInsensitiveSearch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes, AccentInsensitiveSearch;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                $count = 1;
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = Str::slug($category->name) . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
                
                $count = 1;
                while (static::where('slug', $category->slug)->where('id', '!=', $category->id)->exists()) {
                    $category->slug = Str::slug($category->name) . '-' . $count;
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
