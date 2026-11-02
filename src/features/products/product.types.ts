export type Product = {
  id: string
  name: string
  description?: string
  price: number
  category_id: string
  stock: number
  image?: string
  categoryName?: string
  badge?: 'in-stock' | 'low-stock' | 'out-of-stock'
  priceLabel?: string
}

export type CreateProductInput = Omit<Product, 'id'>
