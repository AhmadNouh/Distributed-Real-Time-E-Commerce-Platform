<?php
namespace App\Modules\Products\Repositories;
use App\Modules\Products\Models\Category;
class CategoryRepository { public function all() { return Category::all(); } }
