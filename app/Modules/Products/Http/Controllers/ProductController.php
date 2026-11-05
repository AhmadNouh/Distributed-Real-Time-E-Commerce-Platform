<?php

namespace App\Modules\Products\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use App\Modules\Products\Contracts\ProductService;
use App\Modules\Products\Http\Requests\StoreProductRequest;
use App\Modules\Products\Http\Resources\ProductResource;
use App\Shared\Contracts\InventoryClient;
use App\Shared\Exceptions\WarehouseUnavailableException;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(
        private readonly ProductService $products,
        private readonly InventoryClient $inventory,
    ) {}

    public function index(Request $request)
    {
        $products = $this->products->paginate(
            $request->only(['category_id', 'search']),
            $request->integer('per_page', 15)
        );

        return $this->successDataResponse($products, 'Products retrieved successfully');
    }

    /**
     * Product details include the quantity currently available in the
     * standalone warehouse service (requirement 2, "product details").
     *
     * The warehouse is asked over gRPC. If it is down or too slow, the details
     * page still opens: the stock simply comes back as `null` with
     * `stock_status = "unavailable"` instead of failing the whole request.
     */
    public function show($id)
    {
        $product = $this->products->find((int) $id);

        if (! $product) {
            return $this->notFoundResponse('Product not found');
        }

        [$availableQuantity, $stockStatus] = $this->availableQuantity((int) $product->id);

        return $this->successDataResponse(
            array_merge(ProductResource::make($product)->resolve(), [
                'available_quantity' => $availableQuantity,
                'stock_status' => $stockStatus,
            ]),
            'Product details'
        );
    }

    /**
     * @return array{0: int|null, 1: string}
     */
    private function availableQuantity(int $productId): array
    {
        try {
            return [$this->inventory->getStock($productId), 'available'];
        } catch (WarehouseUnavailableException $e) {
            return [null, 'unavailable'];
        }
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $stock = (int) ($data['stock'] ?? 0);
        $product = $this->products->create($data);

        try {
            $this->inventory->setStock((int) $product->id, $stock);
            $stockStatus = 'available';
        } catch (WarehouseUnavailableException $e) {
            report($e); 
            $stockStatus = 'unavailable';
        }

        return $this->createdResponse(
            array_merge(ProductResource::make($product)->resolve(), ['stock_status' => $stockStatus]),
            'Product created successfully'
        );
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
