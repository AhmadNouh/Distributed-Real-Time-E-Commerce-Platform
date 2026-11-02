
import { ArrowRight, Heart } from 'lucide-react'
import { Link } from 'react-router'
import type { Product } from '../../features/products/product.types'

const badgeStyles = {
  'in-stock': 'bg-[#eaf9f0] text-[#26834d]',
  'low-stock': 'bg-[#fff8e6] text-[#b47616]',
  'out-of-stock': 'bg-[#fff0f0] text-[#c84848]',
}

const badgeLabels = { 'in-stock': 'In stock', 'low-stock': 'Low stock', 'out-of-stock': 'Out of stock' }

export function ProductCard({ product }: { product: Product }) {
  const price = Number(product.price)
const formattedPrice = Number.isFinite(price)
  ? `$${price.toFixed(2)}`
  : 'Price unavailable'
  return (
    <article className="overflow-hidden rounded-lg border border-[#e2e5eb] bg-white">
      <div className="group relative h-[155px] overflow-hidden bg-[#e9e5de]">
        <img src={product.image} alt={product.name} className="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
        <button className="absolute right-3 top-3 grid h-6 w-6 place-items-center rounded-full bg-white/80 text-[#697285] backdrop-blur" aria-label={`Add ${product.name} to wishlist`}>
          <Heart size={12} />
        </button>
      </div>

      <div className="p-4">
        <div className="flex items-center justify-between gap-2">
          <span className="text-xs text-[#8c95a5]">{product.categoryName}</span>
          <span className={`rounded-full px-2 py-1 text-xs ${badgeStyles[product.badge ?? 'in-stock']}`}>
            ● {badgeLabels[product.badge ?? 'in-stock']}
          </span>
        </div>

        <h3 className="mt-2 truncate text-base font-semibold text-[#273044]">
          {product.name}
        </h3>

        <p className="mt-1 truncate text-sm text-[#8992a2]">
          {product.description}
        </p>

       <div className="mt-3 flex items-center justify-between gap-2">
  <strong className="text-base text-[#273044]">
    {formattedPrice}
  </strong>

  <Link
    to={`/store/products/${product.id}`}
    className="flex h-8 items-center gap-1 rounded border border-[#e1e4eb] px-3 text-xs font-medium text-[#273044] transition hover:border-[#8f87ef] hover:text-[#5146dc]"
  >
    View Details
    <ArrowRight size={12} />
  </Link>
</div>
      </div>
    </article>
  )
}
