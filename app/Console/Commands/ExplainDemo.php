<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExplainDemo extends Command
{
    protected $signature   = 'performance:explain';
    protected $description = 'EXPLAIN ANALYZE before/after performance demo';

    public function handle(): int
    {
        $queries = [
            'Orders by status (filtered by index candidate)' =>
                "SELECT * FROM orders WHERE status = 'paid'",

            'Orders by user (paginateFor non-admin path)' =>
                "SELECT * FROM orders WHERE user_id = 1 ORDER BY created_at DESC LIMIT 15",

            'Order items for one order (with items)' =>
                "SELECT * FROM order_items WHERE order_id = 25",

            'Products search (index endpoint)' =>
                "SELECT * FROM products WHERE category_id = 1 AND name ILIKE '%Laptop%'",
        ];

        foreach ($queries as $label => $sql) {
            $this->info("\n=== {$label} ===");
            $rows = DB::select('EXPLAIN (ANALYZE, BUFFERS) ' . $sql);

            foreach ($rows as $row) {
                $line = array_values((array) $row)[0];
                
                foreach (['Scan', 'Planning Time', 'Execution Time', 'Index'] as $keyword) {
                    if (str_contains($line, $keyword)) {
                        $this->line($line);
                        break;
                    }
                }
            }
        }

        return self::SUCCESS;
    }
}