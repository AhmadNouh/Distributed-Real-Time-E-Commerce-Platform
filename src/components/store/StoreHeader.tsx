import { Search, ShoppingCart } from 'lucide-react'
import { Link, NavLink } from 'react-router'
import { useCartStore } from '../../store/cartStore'
import { UserDropdown } from './UserDropdown'

export function StoreHeader() {
  const itemCount = useCartStore((state) =>
    state.cart.reduce((count, item) => count + item.quantity, 0),
  )

  return (
    <header className="border-b border-[#e4e6ec] bg-white">
      <div className="mx-auto flex h-[68px] max-w-[1240px] items-center gap-5 px-4 sm:gap-8 sm:px-6">
        <Link
          to="/store"
          className="flex shrink-0 items-center gap-2 text-lg font-bold tracking-tight text-[#20283a] sm:text-xl"
        >
          <span className="grid h-9 w-9 place-items-center rounded-md bg-[#5146dc] text-sm text-white">
            ⌁
          </span>
          forma
        </Link>

        <nav className="hidden items-center gap-6 text-sm sm:flex">
          <NavLink
            to="/store"
            className={({ isActive }) =>
              isActive
                ? 'font-semibold text-[#5146dc]'
                : 'text-[#5d6679]'
            }
          >
            Shop
          </NavLink>
          <NavLink to="/store/orders" className="text-[#5d6679]">
            My orders
          </NavLink>
        </nav>

        <label className="relative ml-auto hidden w-[255px] md:block">
          <Search
            size={14}
            className="absolute left-3 top-1/2 -translate-y-1/2 text-[#a2a9b7]"
          />
          <input
            className="h-10 w-full rounded-md border border-[#e1e4eb] pl-9 pr-3 text-sm outline-none focus:border-[#8c84ee]"
            placeholder="Search products..."
          />
        </label>

        <div className="flex shrink-0 items-center gap-3 text-[#7d8698] sm:gap-4">
          <Link
            to="/store/cart"
            className="relative"
            aria-label={`Shopping cart, ${itemCount} items`}
          >
            <ShoppingCart size={16} />
            {itemCount > 0 && (
              <span className="absolute -right-3 -top-2 grid h-4 min-w-4 place-items-center rounded-full bg-[#eeefff] px-1 text-[10px] font-medium text-[#5146dc]">
                {itemCount}
              </span>
            )}
          </Link>

          <span className="h-5 w-px bg-[#e6e8ed]" />

          {/* 👈 تم استبدال الكود الثابت (AC و Alex Chen) بمكون UserDropdown التفاعلي */}
          <UserDropdown />
        </div>
      </div>
    </header>
  )
}