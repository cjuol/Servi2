<?php

namespace App\Models;

use App\Traits\AccentInsensitiveSearch;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, HasUuids, AccentInsensitiveSearch;

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
    ];

    // Relaciones
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
