export interface OrderItem {
  id: number | string
  product_name?: string
  name?: string
  quantity: number
  price: number
}

export interface Order {
  id: number | string
  order_number?: string // مثل #FR-1842
  total: number | string
  status: 'Pending' | 'Processing' | 'Completed' | 'Cancelled' | 'Confirmed' | 'Shipped' | 'Delivered'
  created_at: string
  items?: OrderItem[]
  shipping_address?: string
}