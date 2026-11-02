import { useEffect, useState } from 'react'
import { ordersApi } from '../../features/orders/ordersApi'
import type { Order } from '../../features/orders/order.types'

function extractOrders(rawData: any): Order[] {
  if (Array.isArray(rawData)) return rawData
  if (Array.isArray(rawData?.data)) return rawData.data
  if (Array.isArray(rawData?.orders)) return rawData.orders
  if (Array.isArray(rawData?.items)) return rawData.items
  return []
}

export function OrderTable({ compact = false }: { compact?: boolean }) {
  const [orders, setOrders] = useState<Order[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetch() {
      try {
        const res = await ordersApi.list()
        setOrders(extractOrders(res.data))
      } catch (e) {
        console.error(e)
      } finally {
        setLoading(false)
      }
    }
    fetch()
  }, [])

  const rows = compact ? orders.slice(0, 4) : orders

  if (loading) {
    return <div className="p-5 text-base text-[#8992a4]">Loading...</div>
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[820px] text-left text-base">
        <thead className="bg-[#f7f8fa] text-[#7e8798]">
          <tr>
            {[
              "Order",
              "Customer",
              "Items",
              ...(compact ? [] : ["Placed"]),
              "Total",
              "Status",
              ...(compact ? [] : ["Change status"]),
            ].map((head) => (
              <th className="px-5 py-4 font-medium" key={head}>
                {head}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((order: any, index) => {
            const id = order.order_number || `#FR-${order.id}`
            const customer = order.user?.name || order.customer_name || 'Customer'
            const item = order.items?.[0]?.product_name || order.items?.[0]?.name || 'Product'
            const total = order.total ? `$${Number(order.total).toFixed(2)}` : '$0.00'
            const status = order.status || 'Pending'
            const placed = order.created_at ? new Date(order.created_at).toLocaleDateString() : `Oct ${26 - index}, 2026`
            const itemsCount = order.items?.length || 1
            return (
              <tr className="border-t border-[#edf0f3]" key={order.id}>
                <td className="px-5 py-4 font-medium text-[#5146dc]">{id}</td>
                <td className="px-5 py-4">
                  {customer}
                  <small className="mt-1 block text-[#8992a4]">
                    {order.user?.email || `${customer.toLowerCase().replace(" ", ".")}@email.com`}
                  </small>
                </td>
                <td className="px-5 py-4">
                  {item}
                  <small className="mt-1 block text-[#8992a4]">
                    {itemsCount} item{itemsCount > 1 ? 's' : ''}
                  </small>
                </td>
                {!compact && (
                  <td className="px-5 py-4 text-[#8992a4]">
                    {placed}
                  </td>
                )}
                <td className="px-5 py-4">{total}</td>
                <td className="px-5 py-4">
                  <span
                    className={`rounded-full px-2.5 py-1 text-base ${status === "Completed" ? "bg-[#eaf9f0] text-[#26834d]" : status === "Processing" ? "bg-[#eef7ff] text-[#2876c5]" : "bg-[#fff8e6] text-[#b47616]"}`}
                  >
                    ● {status}
                  </span>
                </td>
                {!compact && (
                  <td className="px-5 py-4">
                    <select className="rounded border border-[#e1e4eb] px-2 py-1.5 text-base">
                      <option>{status}</option>
                      <option>Pending</option>
                      <option>Processing</option>
                      <option>Completed</option>
                      <option>Cancelled</option>
                    </select>
                    <button className="ml-2 rounded border border-[#e1e4eb] px-3 py-1.5 text-base">
                      Save
                    </button>
                  </td>
                )}
              </tr>
            )
          })}
        </tbody>
      </table>
    </div>
  );
}
