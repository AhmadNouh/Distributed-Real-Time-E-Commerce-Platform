<?php
namespace App\Modules\Users\Services;

use App\Modules\Users\Models\User;

interface UserService
{
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
}
