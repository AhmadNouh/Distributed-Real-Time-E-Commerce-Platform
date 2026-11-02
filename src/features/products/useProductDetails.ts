import { useEffect, useState } from 'react'
import { useParams } from 'react-router'
import { productsApi } from './productsApi'
import type { Product } from './product.types'

export function useProductDetails(customId?: string) {
  // التقاط الـ id من الـ URL بارامتر (تأكد أن مسار الراوتر لديك يحمل :id)
  const params = useParams<{ id?: string }>()
  const resolvedId = customId || params.id

  const [product, setProduct] = useState<Product | null>(null)
  const [isLoading, setIsLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!resolvedId) {
      setIsLoading(false)
      setError('Invalid product ID.')
      return
    }

    const id = resolvedId

    async function fetchProduct() {
      try {
        setIsLoading(true)
        setError(null)

        const response = await productsApi.getById(id)
        const rawData = response.data
        const productData: Product = (rawData as any)?.data ?? rawData

        setProduct(productData)
      } catch (err) {
        console.error('Failed to fetch product details:', err)
        setError('تعذر تحميل تفاصيل المنتج من الخادم.')
      } finally {
        setIsLoading(false)
      }
    }

    fetchProduct()
  }, [resolvedId])

  return { product, isLoading, error }
}