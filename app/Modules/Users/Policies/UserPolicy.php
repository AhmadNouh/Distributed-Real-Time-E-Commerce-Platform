<?php
namespace App\Modules\Users\Policies;

use App\Modules\Users\Models\User;

class UserPolicy
{
    public function view(User $current, User $user): bool
    {
        return $current->id === $user->id || $current->isAdmin();
    }

    public function update(User $current, User $user): bool
    {
        return $current->id === $user->id || $current->isAdmin();
    }
}
