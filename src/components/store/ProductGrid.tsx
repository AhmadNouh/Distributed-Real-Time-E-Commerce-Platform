
import { SlidersHorizontal } from 'lucide-react'
import type { Product } from '../../features/products/product.types'
import { ProductCard } from './ProductCard'

export function ProductGrid({ products }: { products: Product[] }) {
  return (
    <section className="min-w-0">
      <div className="mb-5 flex items-center justify-between gap-3">
        <div className="flex items-baseline gap-3">
          <h2 className="text-xl font-semibold text-[#20283a] sm:text-2xl">
            All products
          </h2>
          <span className="text-xs text-[#9aa2b1] sm:text-sm">
            {products.length} products
          </span>
        </div>

        <button className="flex shrink-0 items-center gap-2 rounded border border-[#e1e4eb] bg-white px-3 py-2 text-xs text-[#586276] sm:px-4 sm:text-sm">
          <SlidersHorizontal size={14} />
          Sort: Featured
        </button>
      </div>

      {products.length === 0 ? (
        <div className="rounded-lg border border-dashed border-[#dfe2e9] py-20 text-center text-xs text-[#8c95a5]">
          No products match your filters.
        </div>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}

      <p className="mt-6 text-center text-xs text-[#9ca4b3] sm:text-sm">
        You're all caught up. Six essentials, thoughtfully selected.
      </p>
    </section>
  )
}
