<?php

namespace App\Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Modules\Products\Models\Product;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['id' => 1,  'name' => 'ASUS TUF Laptop',             'price' => 1200.00, 'description' => 'High performance developer laptop',    'category_id' => 1, 'c1' => '#1e3a8a', 'c2' => '#3b82f6'],
            ['id' => 2,  'name' => 'Mechanical Keyboard',         'price' => 75.00,   'description' => 'RGB mechanical gaming keyboard',       'category_id' => 2, 'c1' => '#7c2d12', 'c2' => '#f59e0b'],
            ['id' => 3,  'name' => 'Wireless Mouse',              'price' => 25.00,   'description' => 'Ergonomic 2.4GHz wireless mouse',      'category_id' => 2, 'c1' => '#14532d', 'c2' => '#22c55e'],
            ['id' => 4,  'name' => '4K Monitor 27 inch',          'price' => 320.00,  'description' => '27 inch 4K UHD IPS display',           'category_id' => 1, 'c1' => '#581c87', 'c2' => '#a855f7'],
            ['id' => 5,  'name' => 'USB-C Hub',                   'price' => 45.00,   'description' => '8-in-1 USB-C multiport adapter',       'category_id' => 2, 'c1' => '#0f766e', 'c2' => '#2dd4bf'],
            ['id' => 6,  'name' => 'Noise Cancelling Headphones', 'price' => 199.00,  'description' => 'Over-ear ANC wireless headphones',     'category_id' => 2, 'c1' => '#7f1d1d', 'c2' => '#ef4444'],
            ['id' => 7,  'name' => 'SSD 1TB NVMe',                'price' => 89.00,   'description' => '1TB NVMe PCIe Gen4 solid state drive', 'category_id' => 1, 'c1' => '#1e293b', 'c2' => '#64748b'],
            ['id' => 8,  'name' => 'Webcam 1080p',                'price' => 39.00,   'description' => 'Full HD webcam with microphone',       'category_id' => 2, 'c1' => '#3f6212', 'c2' => '#84cc16'],
            ['id' => 9,  'name' => 'Laptop Stand',                'price' => 30.00,   'description' => 'Aluminum adjustable laptop stand',     'category_id' => 2, 'c1' => '#9a3412', 'c2' => '#fb923c'],
            ['id' => 10, 'name' => 'Desk Microphone',             'price' => 60.00,   'description' => 'USB condenser streaming microphone',   'category_id' => 2, 'c1' => '#4c1d95', 'c2' => '#c084fc'],
        ];

        foreach ($products as $p) {
            $fileName = "product-{$p['id']}.svg";
            $this->storeImage($fileName, $p['name'], $p['c1'], $p['c2']);

            Product::updateOrCreate(
                ['id' => $p['id']],
                [
                    'name'        => $p['name'],
                    'price'       => $p['price'],
                    'description' => $p['description'],
                    'category_id' => $p['category_id'],
                    'image'  => "products/{$fileName}",
                ]
            );
        }

        $this->syncSequence();
    }

    /**
     * The rows above are seeded with explicit ids, which does not advance the
     * PostgreSQL sequence. Without this the next product created through the
     * API would reuse an existing id and fail with a duplicate key error.
     */
    private function syncSequence(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('products', 'id'), COALESCE((SELECT MAX(id) FROM products), 1))"
        );
    }

    private function storeImage(string $fileName, string $label, string $c1, string $c2): void
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$c1}"/>
      <stop offset="100%" stop-color="{$c2}"/>
    </linearGradient>
  </defs>
  <rect width="600" height="400" fill="url(#g)"/>
  <rect x="40" y="40" width="520" height="320" fill="none" stroke="#ffffff" stroke-opacity="0.35" stroke-width="2"/>
  <text x="300" y="195" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="#ffffff" text-anchor="middle">{$label}</text>
  <text x="300" y="240" font-family="Arial, sans-serif" font-size="18" fill="#ffffff" fill-opacity="0.8" text-anchor="middle">product image</text>
</svg>
SVG;
        $dir = public_path('products');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents("{$dir}/{$fileName}", $svg);
    }
}