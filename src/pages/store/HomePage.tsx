import { CategorySidebar } from '../../components/store/CategorySidebar'
import { HeroBanner } from '../../components/store/HeroBanner'
import { ProductGrid } from '../../components/store/ProductGrid'
import { useProductCatalog } from '../../features/products/useProductCatalog'

export function HomePage() {
  const catalog = useProductCatalog()

  return (
    <main className="mx-auto max-w-[1240px] px-5 py-5">
      <HeroBanner />
      <div className="mt-6 grid gap-6 lg:grid-cols-[145px_1fr]">
        <CategorySidebar
          categories={catalog.categories}
          selectedCategory={catalog.selectedCategory}
          maxPrice={catalog.maxPrice}
          inStockOnly={catalog.inStockOnly}
          onCategoryChange={catalog.setSelectedCategory}
          onMaxPriceChange={catalog.setMaxPrice}
          onStockChange={catalog.setInStockOnly}
        />
        <ProductGrid products={catalog.products} />
      </div>
    </main>
  )
}
