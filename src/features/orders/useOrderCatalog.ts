import { useEffect, useMemo, useState } from 'react'
import { ordersApi } from './ordersApi'
import type { Order } from './order.types'

function extractOrders(rawData: any): Order[] {
  const data = rawData?.data ?? rawData
  const list = Array.isArray(data)
    ? data
    : data?.data ?? data?.orders ?? data?.items ?? data?.result

  if (!Array.isArray(list)) return []

  return list.map((order) => ({
    ...order,
    status: String(order.status ?? 'pending').replace(/^./, (letter: string) => letter.toUpperCase()) as Order['status'],
  }))
}

export function useOrderCatalog() {
  const [orders, setOrders] = useState<Order[]>([])
  const [selectedStatus, setSelectedStatus] = useState<string>('all')
  const [isLoading, setIsLoading] = useState<boolean>(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    async function fetchOrders() {
      try {
        setIsLoading(true)
        setError(null)

        const response = await ordersApi.list()
        const ordersList = extractOrders(response.data)
        setOrders(ordersList)
      } catch (err) {
        console.error('Failed to fetch orders:', err)
        setError('تعذر تحميل الطلبات من الخادم.')
      } finally {
        setIsLoading(false)
      }
    }

    fetchOrders()
  }, [])

  const statusCounts = useMemo(() => {
    const counts: Record<string, number> = { all: orders.length }
    orders.forEach((order) => {
      const status = order.status || 'Pending'
      counts[status] = (counts[status] || 0) + 1
    })
    return counts
  }, [orders])

  const filteredOrders = useMemo(() => {
    if (selectedStatus === 'all') return orders
    return orders.filter((order) => order.status?.toLowerCase() === selectedStatus.toLowerCase())
  }, [orders, selectedStatus])

  const activeOrder = filteredOrders[0] || orders[0]

  return {
    orders: filteredOrders,
    allOrdersCount: orders.length,
    statusCounts,
    selectedStatus,
    isLoading,
    error,
    setSelectedStatus,
    activeOrder,
  }
}