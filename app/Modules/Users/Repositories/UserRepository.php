<?php
namespace App\Modules\Users\Repositories;
use App\Modules\Users\Models\User;
class UserRepository { public function findById($id): ?User { return User::find($id); } }
