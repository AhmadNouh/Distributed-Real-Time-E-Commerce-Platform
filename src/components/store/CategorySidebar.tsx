
import { MessageCircle } from 'lucide-react'

type CategorySidebarProps = {
  categories: { id: string; name: string; count: number }[]
  selectedCategory: string |null;
  maxPrice: number
  inStockOnly: boolean
  onCategoryChange: (id: string) => void
  onMaxPriceChange: (price: number) => void
  onStockChange: (value: boolean) => void
}

export function CategorySidebar({
  categories,
  selectedCategory,
  maxPrice,
  inStockOnly,
  onCategoryChange,
  onMaxPriceChange,
  onStockChange,
}: CategorySidebarProps) {
  return (
    <aside className="space-y-7">
      <div>
        <h2 className="mb-3 text-base font-semibold text-[#273044]">
          Categories
        </h2>

        <div className="space-y-1">
          {categories.map((category) => (
            <button
              key={category.id?? category}
              onClick={() => onCategoryChange(category.id)}
              className={`flex w-full items-center justify-between rounded px-2 py-2 text-left text-sm transition ${
                selectedCategory === category.id
                  ? 'bg-[#eeedff] font-medium text-[#5146dc]'
                  : 'text-[#6f788b] hover:bg-[#f5f5fa]'
              }`}
            >
              {category.name}
              <span
                className={
                  selectedCategory === category.id
                    ? 'text-[#756ce0]'
                    : 'text-[#9ca4b3]'
                }
              >
                {category.count}
              </span>
            </button>
          ))}
        </div>
      </div>

      <div className="border-t border-[#e6e8ed] pt-5">
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-base font-semibold text-[#273044]">
            Price range
          </h2>
          <span className="text-sm text-[#5146dc]">${maxPrice}</span>
        </div>

        <input
          type="range"
          min="0"
          max="200"
          value={maxPrice}
          onChange={(event) => onMaxPriceChange(Number(event.target.value))}
          className="h-1 w-full accent-[#5146dc]"
        />

        <div className="mt-2 flex justify-between text-xs text-[#9ca4b3]">
          <span>$0</span>
          <span>$200</span>
        </div>
      </div>

      <div className="border-t border-[#e6e8ed] pt-5">
        <h2 className="mb-3 text-base font-semibold text-[#273044]">
          Availability
        </h2>

        <label className="flex items-center gap-2 text-sm text-[#7d8698]">
          <input
            type="checkbox"
            checked={inStockOnly}
            onChange={(event) => onStockChange(event.target.checked)}
            className="h-3 w-3 accent-[#5146dc]"
          />
          In stock only
        </label>
      </div>

      <div className="rounded-lg border border-[#e5e7ed] bg-white p-3">
        <MessageCircle size={16} className="text-[#5146dc]" />

        <h3 className="mt-2 text-sm font-semibold text-[#273044]">
          A little help?
        </h3>

        <p className="mt-1 text-xs leading-5 text-[#8a92a2]">
          Find the right fit for your everyday.
        </p>

        <a
          href="#help"
          className="mt-2 block text-sm font-medium text-[#5146dc]"
        >
          Talk to our team →
        </a>
      </div>
    </aside>
  )
}
