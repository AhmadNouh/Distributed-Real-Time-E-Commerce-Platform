<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Modules\Products\Http\Requests\StoreProductRequest;
use App\Modules\Products\Http\Resources\ProductResource;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return $this->successDataResponse($query->paginate(15), 'Products retrieved successfully');
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        return $this->successDataResponse($product, 'Product details');
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return $this->createdResponse(ProductResource::make($product), 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        $product->update($request->all());

        return $this->successDataResponse($product, 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return $this->successResponse('Product deleted successfully');
    }
}