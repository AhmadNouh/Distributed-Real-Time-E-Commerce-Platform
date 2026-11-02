<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use App\Modules\Products\Contracts\ProductService;
use App\Modules\Products\Http\Requests\StoreProductRequest;
use App\Modules\Products\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(
        private readonly ProductService $products
    ) {}

    public function index(Request $request)
    {
        $products = $this->products->paginate(
            $request->only(['category_id', 'search']),
            $request->integer('per_page', 15)
        );

        return $this->successDataResponse($products, 'Products retrieved successfully');
    }

    public function show($id)
    {
        $product = $this->products->find((int) $id);

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

        $product = $this->products->create($data);

        return $this->createdResponse(ProductResource::make($product), 'Product created successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = $this->products->update((int) $id, $data);

        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        return $this->successDataResponse($product, 'Product updated successfully');
    }

    public function destroy($id)
    {
        if (! $this->products->delete((int) $id)) {
            return $this->notFoundResponse('Product not found');
        }

        return $this->successResponse('Product deleted successfully');
    }
}
