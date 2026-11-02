<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Contracts\ProductService;
use App\Modules\Products\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProductServiceImpl implements ProductService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query();

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->find($id);

        if (! $product) {
            return null;
        }

        $product->update($data);

        return $product;
    }

    public function delete(int $id): bool
    {
        $product = $this->find($id);

        if (! $product) {
            return false;
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        return (bool) $product->delete();
    }

    public function priceOf(int $productId): float
    {
        return (float) ($this->find($productId)?->price ?? 0);
    }
}
