<?php

namespace App\Modules\Products\Contracts;

use App\Modules\Products\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Public contract of the Products module.
 *
 * Other modules (e.g. Orders) must depend on this interface only,
 * never on the Product model or its database table directly.
 */
interface ProductService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): ?Product;

    public function delete(int $id): bool;

    public function findMany(array $ids): Collection;
    
    public function priceOf(int $productId): float;
}
