import { Truck } from 'lucide-react'
import { Link } from 'react-router'
import { useOrderCatalog } from '../../features/orders/useOrderCatalog'

export function OrdersPage() {
  const {
    orders,
    statusCounts,
    selectedStatus,
    isLoading,
    error,
    setSelectedStatus,
    activeOrder,
  } = useOrderCatalog()

  if (isLoading) {
    return <div className="py-20 text-center text-sm text-gray-500">Loading your orders...</div>
  }

  if (error) {
    return <div className="py-20 text-center text-sm text-red-500">{error}</div>
  }

  return (
    <main className="mx-auto max-w-[1120px] px-5 py-8">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold">Your orders</h1>
          <p className="mt-1 text-sm text-[#7d8698] sm:text-base">
            From our store to your door. Keep an eye on every step.
          </p>
        </div>

        <Link
          to="/store"
          className="rounded border border-[#e1e4eb] px-3 py-2 text-sm transition hover:bg-gray-50"
        >
          ← Continue shopping
        </Link>
      </div>

      {/* أزرار الفلترة */}
      <div className="mt-6 flex flex-wrap gap-2">
        {['all', 'Pending', 'Processing', 'Completed'].map((status) => {
          const label = status === 'all' ? 'All orders' : status
          const count = statusCounts[status] || 0
          const isActive = selectedStatus.toLowerCase() === status.toLowerCase()

          return (
            <button
              key={status}
              onClick={() => setSelectedStatus(status)}
              className={`rounded px-3 py-2 text-sm transition ${
                isActive
                  ? 'bg-[#5146dc] text-white'
                  : 'bg-white border border-[#e1e4eb] text-[#273044] hover:bg-gray-50'
              }`}
            >
              {label} {count}
            </button>
          )
        })}
      </div>

      {/* جدول الطلبات */}
      <section className="mt-5 overflow-hidden rounded-lg border border-[#e2e5eb] bg-white">
        <div className="overflow-x-auto">
          <table className="w-full min-w-[700px] text-left text-sm">
            <thead className="bg-[#f7f8fa] text-[#7e8798]">
              <tr>
                <th className="px-4 py-3">Order</th>
                <th className="px-4 py-3">Items</th>
                <th className="px-4 py-3">Date placed</th>
                <th className="px-4 py-3">Total</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>

            <tbody>
              {orders.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-10 text-center text-sm text-[#8992a4]">
                    No orders found.
                  </td>
                </tr>
              ) : (
                orders.map((order) => {
                  const orderId = order.order_number || `#FR-${order.id}`
                  const firstItemName = order.items?.[0]?.product_name || order.items?.[0]?.name || 'Store Product'
                  const itemsCount = order.items?.length || 1
                  const formattedDate = new Date(order.created_at).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                  })
                  const status = order.status || 'Pending'

                  return (
                    <tr className="border-t border-[#edf0f3]" key={order.id}>
                      <td className="px-4 py-4 font-medium text-[#5146dc]">
                        {orderId}
                      </td>
                      <td className="px-4 py-4">
                        {firstItemName}
                        <small className="mt-1 block text-xs text-[#8992a4]">
                          {itemsCount} item{itemsCount > 1 ? 's' : ''}
                        </small>
                      </td>
                      <td className="px-4 py-4 text-[#8992a4]">{formattedDate}</td>
                      <td className="px-4 py-4 font-medium">${Number(order.total).toFixed(2)}</td>
                      <td className="px-4 py-4">
                        <span
                          className={`whitespace-nowrap rounded-full px-2 py-1 text-xs ${
                            status === "Completed"
                              ? "bg-[#eaf9f0] text-[#26834d]"
                              : status === "Processing"
                              ? "bg-[#eef7ff] text-[#2876c5]"
                              : "bg-[#fff8e6] text-[#b47616]"
                          }`}
                        >
                          ● {status}
                        </span>
                      </td>
                      <td className="px-4 py-4">
                        <button className="rounded border border-[#e1e4eb] px-3 py-1.5 text-xs transition hover:bg-gray-50">
                          Details
                        </button>
                      </td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>

        <p className="p-4 text-sm text-[#8992a4]">
          Showing {orders.length} orders
        </p>
      </section>

      {/* قسم تتبع الطلب النشط */}
      {activeOrder && (
        <div className="mt-5 grid gap-4 lg:grid-cols-[1fr_230px]">
          <section className="rounded-lg border border-[#e2e5eb] bg-white p-5">
            <div className="flex flex-wrap justify-between gap-3">
              <div>
                <h2 className="text-sm font-semibold">
                  A little closer to your door
                </h2>
                <p className="mt-1 text-xs text-[#8992a4] sm:text-sm">
                  Order {activeOrder.order_number || `#FR-${activeOrder.id}`} · Placed {new Date(activeOrder.created_at).toLocaleDateString()}
                </p>
              </div>

              <span className="h-fit whitespace-nowrap rounded-full bg-[#eef7ff] px-2 py-1 text-xs text-[#2876c5]">
                ● {activeOrder.status || 'Processing'}
              </span>
            </div>

            <div className="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
              {[
                ["✓", "Order confirmed"],
                ["◈", "Being prepared"],
                ["⌁", "On its way"],
                ["⌂", "Delivered"],
              ].map(([icon, label], index) => (
                <div key={label} className="text-sm">
                  <span
                    className={`grid h-6 w-6 place-items-center rounded-full ${
                      index < 2
                        ? "bg-[#eeedff] text-[#5146dc]"
                        : "bg-[#f4f5f7] text-[#a3aab7]"
                    }`}
                  >
                    {icon}
                  </span>
                  <b className="mt-2 block text-sm">{label}</b>
                  <small className="mt-1 block text-xs leading-5 text-[#8992a4]">
                    {index < 2 ? "Confirmed" : "Tracking available soon"}
                  </small>
                </div>
              ))}
            </div>

            <div className="mt-5 border-t border-[#edf0f3] pt-4 text-sm text-[#7d8698]">
              Shipping to
              <br />
              <b className="text-[#273044]">
                {activeOrder.shipping_address || 'Customer Address · Verified'}
              </b>
            </div>
          </section>

          <aside className="rounded-lg border border-[#e2e5eb] bg-white p-5">
            <h2 className="text-sm font-semibold">We’re here to help</h2>
            <p className="mt-8 text-sm leading-5 text-[#8992a4]">
              Questions about your order, delivery, or a return? Our team has you
              covered.
            </p>
            <button className="mt-4 w-full rounded border border-[#e1e4eb] py-2 text-sm transition hover:bg-gray-50">
              <Truck size={14} className="mr-1 inline" />
              Contact support
            </button>
          </aside>
        </div>
      )}
    </main>
  )
}