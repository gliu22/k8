<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // Only admins can view user list
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        // Admins can view any user in their organization
        return $user->isAdmin() && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        // Only admins can create users
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Admins can update users in their organization
        // Users cannot update themselves through this endpoint (use profile update instead)
        return $user->isAdmin() &&
               $user->organization_id === $model->organization_id &&
               $user->id !== $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        // Admins can delete users in their organization
        // Cannot delete yourself
        return $user->isAdmin() &&
               $user->organization_id === $model->organization_id &&
               $user->id !== $model->id;
    }
}
