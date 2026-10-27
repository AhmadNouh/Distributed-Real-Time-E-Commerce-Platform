<?php
namespace App\Modules\Products\Policies;
use App\Modules\Users\Models\User;
class ProductPolicy
{
    public function viewAny(User $user) { return true; }
    public function view(User $user, $model) { return true; }
    public function create(User $user) { return $user->isAdmin(); }
    public function update(User $user, $model) { return $user->isAdmin(); }
    public function delete(User $user, $model) { return $user->isAdmin(); }
}
