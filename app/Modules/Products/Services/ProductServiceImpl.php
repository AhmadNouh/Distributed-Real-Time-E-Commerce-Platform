<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Contracts\ProductService;
use App\Modules\Products\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ProductServiceImpl implements ProductService
{
    // public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    // {
    //     $query = Product::query();

    //     if (! empty($filters['category_id'])) {
    //         $query->where('category_id', $filters['category_id']);
    //     }

    //     if (! empty($filters['search'])) {
    //         $query->where('name', 'like', '%' . $filters['search'] . '%');
    //     }

    //     return $query->paginate($perPage);
    // }

    
    private const CACHE_TTL = 300;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {        
        $page = (int) request('page', 1);

        $cacheKey = 'products:list:' . md5(
            strtolower((string) ($filters['search'] ?? ''))
            . '|'
            . ($filters['category_id'] ?? 'all')
            . '|'
            . $perPage
            . '|'
            . $page
        );

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
            $query = Product::query();

            if (! empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (! empty($filters['search'])) {
                $query->where('name', 'like', '%' . $filters['search'] . '%');
            }

            return $query->paginate($perPage);
        });
    }

    private function flushProductsCache(): void
    {
        $redis = \Illuminate\Support\Facades\Redis::connection('cache');

        $keys = $redis->keys('*products:list:*');

        if ($keys !== []) {
            $redis->del(...$keys);
        }
    }


    public function create(array $data): Product
    {
        $product = Product::create($data);
        $this->flushProductsCache();
        return $product;
    }

    public function update(int $id, array $data): ?Product
    {
        $product = $this->find($id);

        if (! $product) {
            return null;
        }

        $product->update($data);
        $this->flushProductsCache();
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

        $product = (bool) $product->delete();
        $this->flushProductsCache();
        return $product;
    }

    public function priceOf(int $productId): float
    {
        return (float) ($this->find($productId)?->price ?? 0);
    }

    public function find(int $id): ?Product // with N+1
    {
        return Product::find($id);
    }

    public function findMany(array $ids): Collection // solve N+1
    {
        return Product::whereIn('id', $ids)->get()->keyBy('id');
    }
}
