<?php
namespace App\Modules\Products\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\Storage;
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->has('category_id')) $query->where('category_id', $request->category_id);
        if ($request->has('search')) $query->where('name', 'like', '%' . $request->search . '%');
        return response()->json($query->paginate(15));
    }
    public function show($id) { $p = Product::find($id); if(!$p) return response()->json(['error'=>['message'=>'Not found','code'=>404,'details'=>[]]],404); return $p; }
    public function store(Request $request)
    {
        $request->validate(['name'=>'required','description'=>'nullable','price'=>'required|numeric','category_id'=>'required|exists:categories,id','image'=>'nullable|image']);
        $data = $request->all();
        if($request->hasFile('image')) $data['image'] = $request->file('image')->store('products', 'public');
        $p = Product::create($data);
        return response()->json($p, 201);
    }
    public function update(Request $request, $id) { $p = Product::findOrFail($id); $p->update($request->all()); return $p; }
    public function destroy($id) { Product::findOrFail($id)->delete(); return response()->json(null,204); }
}
