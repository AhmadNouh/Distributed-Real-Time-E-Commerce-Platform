import { api } from '../../lib/api'
import type { CreateProductInput, Product } from './product.types'

export interface Category {
  id: number | string
  name: string
}

export const productsApi = {
  list: (params?: Record<string, any>) =>
    api.get<Product[]>('/products', { params }),
  getById: (id: string | number) => api.get<Product>(`/products/${id}`),
  categories: () => api.get<Category[]>('/products/categories'),
  create: (product: CreateProductInput | FormData) => api.post<Product>('/products', product),
  update: (id: string | number, product: Partial<CreateProductInput>) =>
    api.put<Product>(`/products/${id}`, product),
  delete: (id: string | number) => api.delete(`/products/${id}`),
}
