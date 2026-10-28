<?php
namespace App\Modules\Products\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Category;
class CategoryController extends Controller
{
    public function index() { return response()->json(Category::all()); }
}
