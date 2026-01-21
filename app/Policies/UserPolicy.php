<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Solo admins pueden borrar usuarios
        // Y no pueden borrarse a sí mismos
        return $user->role === UserRole::ADMIN && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Solo admins pueden forzar el borrado
        // Y no pueden borrarse a sí mismos
        return $user->role === UserRole::ADMIN && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can restore multiple instances of the model.
     */
    public function restoreAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete multiple instances of the model.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
