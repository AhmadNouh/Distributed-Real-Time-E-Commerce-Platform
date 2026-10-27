<?php
namespace App\Modules\Products\Services;
use App\Modules\Products\Repositories\ProductRepository;
class ProductServiceImpl implements ProductService
{
    protected $repo;
    public function __construct(ProductRepository $repo) { $this->repo = $repo; }
    public function list(array $filters) { return \App\Modules\Products\Models\Product::where('name','like','%'.($filters['search']??'').'%')->paginate(15); }
    public function find($id) { return $this->repo->find($id); }
    public function create(array $data) { return \App\Modules\Products\Models\Product::create($data); }
    public function update($id, array $data) { $p = $this->repo->find($id); $p->update($data); return $p; }
    public function delete($id) { $p = $this->repo->find($id); $p->delete(); return true; }
}
