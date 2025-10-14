<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $currentUser  // The user who is logged in (e.g., the Admin Aide)
     * @param  \App\Models\User  $targetUser   // The user being viewed (e.g., the Vendor)
     * @return bool
     */
    public function view(User $currentUser, User $targetUser)
{
    // Rule: Allow Admins and Staff (Admin Aides) to view any user's data.
    if ($currentUser->isAdminSupervisor() || $currentUser->isAdminAide()) {
        return true;
    }

    // Rule: A regular user (Vendor) can only view their own data.
    return $currentUser->id === $targetUser->id;
}
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
