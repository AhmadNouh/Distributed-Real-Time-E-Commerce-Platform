import { api } from '../../lib/api'
import type { Order } from './order.types'

export interface CreateOrderItem {
  product_id: number | string
  quantity: number
}

export interface CreateOrderInput {
  items: CreateOrderItem[]
}

export const ordersApi = {
  create: (order: CreateOrderInput) => api.post<Order>('/orders', order),
  list: () => api.get<Order[]>('/orders'),
  getById: (id: string | number) => api.get<Order>(`/orders/${id}`),
  updateStatus: (id: string | number, status: string) =>
    api.patch<Order>(`/orders/${id}/status`, { status }),
}