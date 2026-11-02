import { useEffect, useMemo, useState } from 'react'
import { productsApi } from './productsApi'
import type { Product } from './product.types'

export interface Category {
  id: string
  name: string
  count: number
}

function extractProducts(rawData: any): Product[] {
  if (Array.isArray(rawData)) return rawData
  if (Array.isArray(rawData?.data)) return rawData.data
  if (Array.isArray(rawData?.data?.data)) return rawData.data.data
  if (Array.isArray(rawData?.products)) return rawData.products
  if (Array.isArray(rawData?.data?.products)) return rawData.data.products
  if (Array.isArray(rawData?.items)) return rawData.items
  if (Array.isArray(rawData?.data?.items)) return rawData.data.items
  if (Array.isArray(rawData?.result)) return rawData.result
  if (Array.isArray(rawData?.data?.result)) return rawData.data.result
  return []
}

export function useProductCatalog() {
  const [products, setProducts] = useState<Product[]>([])
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [maxPrice, setMaxPrice] = useState(1000)
  const [inStockOnly, setInStockOnly] = useState(false)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchCatalogData() {
      try {
        setIsLoading(true)
        setError(null)

        const response = await productsApi.list()
        const productsList = extractProducts(response.data)
        console.log("products:",productsList)
        setProducts(productsList)
      } catch (err) {
        console.error('Failed to fetch product catalog:', err)
        setError('تعذر تحميل المنتجات من الخادم.')
      } finally {
        setIsLoading(false)
      }
    }

    fetchCatalogData()
  }, [])

  const categories: Category[] = useMemo(() => {
    const countsMap: Record<string, number> = { all: products.length }

    products.forEach((product) => {
      const catId = String(product.category_id ?? (product as any).category_id ?? (product as any).category?.id ?? 'general')
      countsMap[catId] = (countsMap[catId] || 0) + 1
    })

    const uniqueCats: Category[] = [
      { id: 'all', name: 'All Categories', count: products.length },
      ...Object.keys(countsMap)
        .filter((id) => id !== 'all')
        .map((id) => ({
          id,
          name: id.charAt(0).toUpperCase() + id.slice(1),
          count: countsMap[id],
        })),
    ]

    return uniqueCats
  }, [products])

  const filteredProducts = useMemo(() => {
    return products.filter((product) => {
      const productCategory = String(product.category_id ?? (product as any).category?.id ?? (product as any).category_id ?? 'all')
      const categoryMatches = selectedCategory === 'all' || productCategory === String(selectedCategory)
      const priceMatches = Number(product.price) <= Number(maxPrice)
      const stockMatches = !inStockOnly || Number(product.stock) > 0

      return categoryMatches && priceMatches && stockMatches
    })
  }, [products, inStockOnly, maxPrice, selectedCategory])

  return {
    categories,
    products: filteredProducts,
    selectedCategory,
    maxPrice,
    inStockOnly,
    isLoading,
    error,
    setSelectedCategory,
    setMaxPrice,
    setInStockOnly,
  }
}