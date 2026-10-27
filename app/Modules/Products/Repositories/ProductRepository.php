<?php
namespace App\Modules\Products\Repositories;
use App\Modules\Products\Models\Product;
class ProductRepository { public function find($id) { return Product::find($id); } }
