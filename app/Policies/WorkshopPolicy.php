<?php

namespace App\Policies;

use App\Models\Workshop;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkshopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Workshop $workshop): bool
    {
        return in_array($workshop->status, ['confirmed', 'launched', 'progress', 'finished']) || $user?->hasRole('admin') || $user?->hasRole('hod') || $workshop->organiser_id == $user?->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Workshop $workshop): bool
    {
        return $user->hasRole('admin') || $user->hasRole('hod') || $workshop->organiser_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Workshop $workshop): bool
    {
        return $user->hasRole('admin') || $workshop->organiser_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Workshop $workshop): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Workshop $workshop): bool
    {
        return $user->hasRole('admin');
    }
}
