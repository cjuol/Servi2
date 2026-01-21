<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- 1. IMPORTANTE: Añadida esta línea

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- INICIO DEL FIX HTTPS ---
        // Esto fuerza a Laravel a generar todos los links (CSS, JS, Rutas) con https://
        if (config('app.env') !== 'local') { 
            URL::forceScheme('https');

            // Fix adicional para Nginx Reverse Proxy:
            // Fuerza a la request actual a creer que es HTTPS para validar firmas y assets
            $this->app['request']->server->set('HTTPS', 'on');
        }
        // --- FIN DEL FIX HTTPS ---


        // Registrar policies
        Gate::policy(User::class, UserPolicy::class);

        // Extender el Builder de Eloquent para búsquedas insensibles a acentos
        Builder::macro('whereLikeUnaccented', function ($column, $value) {
            if (config('database.default') === 'pgsql') {
                return $this->whereRaw("unaccent(LOWER({$column}::text)) LIKE unaccent(LOWER(?))", ["%{$value}%"]);
            }
            
            // Para MySQL/MariaDB con utf8mb4_unicode_ci ya es insensible a acentos
            return $this->where($column, 'ILIKE', "%{$value}%");
        });

        Builder::macro('orWhereLikeUnaccented', function ($column, $value) {
            if (config('database.default') === 'pgsql') {
                return $this->orWhereRaw("unaccent(LOWER({$column}::text)) LIKE unaccent(LOWER(?))", ["%{$value}%"]);
            }
            
            return $this->orWhere($column, 'ILIKE', "%{$value}%");
        });

        // Sobrescribir el comportamiento de búsqueda global de Filament
        Builder::macro('searchUnaccented', function (array $columns, $search) {
            return $this->where(function ($query) use ($columns, $search) {
                foreach ($columns as $column) {
                    if (config('database.default') === 'pgsql') {
                        $query->orWhereRaw("unaccent(LOWER({$column}::text)) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                    } else {
                        $query->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                }
            });
        });
    }
}