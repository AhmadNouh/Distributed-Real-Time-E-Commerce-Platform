import {
  AlertTriangle,
  Box,
  CalendarDays,
  ChevronRight,
  DollarSign,
  ShoppingBag,
  Users,
} from "lucide-react";
import { AdminPageHeader } from "../../components/admin/AdminPageHeader";
import { StatCard } from "../../components/admin/StatCard";
import { featuredProducts } from "../../features/products/productData";

export function AdminDashboardPage() {
  const bars = [48, 65, 38, 82, 68, 96, 78];
  return (
    <main className="mx-auto max-w-[1120px] p-5 sm:p-7">
      <AdminPageHeader
        eyebrow="Monday, October 26, 2026"
        title="Good morning, Jordan"
        description="Here's what's happening with your store today."
        action={
          <button className="flex items-center gap-2 rounded-md border border-[#e2e5eb] bg-white px-3 py-2 text-base">
            <CalendarDays size={11} /> Last 7 days
          </button>
        }
      />
      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="Revenue"
          value="$8,420"
          note="↗ 12.8% vs. previous 7 days"
          icon={<DollarSign size={12} />}
        />
        <StatCard
          label="Active orders"
          value="18"
          note="7 pending · 11 processing"
          icon={<ShoppingBag size={12} />}
        />
        <StatCard
          label="Active products"
          value="6"
          note="Across 4 categories"
          icon={<Box size={12} />}
        />
        <StatCard
          label="Inventory alerts"
          value="3"
          note="2 low stock · 1 out of stock"
          icon={<AlertTriangle size={12} />}
        />
      </div>
      <div className="mt-4 grid gap-4 xl:grid-cols-[1fr_250px] ">
        <section className="rounded-lg border border-[#e2e5eb]  bg-white p-4">
          <div className="flex justify-between">
            <div>
              <h2 className="text-xs font-semibold">Revenue overview</h2>
              <p className="mt-1 text-base text-[#8992a4]">
                Daily revenue · Oct 20–26
              </p>
            </div>
            <span className="rounded-full items-center justify-center  bg-[#eaf9f0] px-2 py-1 text-xs text-[#26834d]">
              ● +12.8%
            </span>
          </div>
          <div className="mt-8 flex h-36 items-end justify-around gap-3 border-b border-[#edf0f3]">
            {bars.map((height, index) => (
              <div
                key={index}
                className="flex h-full flex-1 flex-col items-center justify-end gap-2"
              >
                <div
                  style={{ height: `${height}%` }}
                  className={`w-full max-w-9 rounded-t bg-[#d8d5fa] ${index === 6 ? "bg-[#5146dc]" : ""}`}
                />
                <span className="text-sm text-[#8992a4]">
                  {["Tue", "Wed", "Thu", "Fri", "Sat", "Sun", "Mon"][index]}
                </span>
              </div>
            ))}
          </div>
        </section>
        <section className="rounded-lg border border-[#e2e5eb] bg-white p-4">
          <div className="flex justify-between">
            <h2 className="text-xs font-semibold">Needs your attention</h2>
            <span className="rounded-full bg-[#fff8e6] px-2 py-1 text-base text-[#b47616]">
              3
            </span>
          </div>
          {featuredProducts.slice(3, 6).map((product) => (
            <div
              className="flex items-center justify-between border-b border-[#edf0f3] py-3 text-sm"
              key={product.id}
            >
              
              <span>
                {product.name}
                <small
                  className={`mt-1 block ${product.stock ? "text-[#b47616]" : "text-[#c84848]"}`}
                >
                  {product.stock
                    ? `${product.stock} remaining`
                    : "Out of stock"}
                </small>
              </span>
              <ChevronRight size={11} className="text-[#8992a4]" />
            </div>
          ))}
          <a
            href="/admin/inventory"
            className="mt-3 block text-sm font-medium text-[#5146dc]"
          >
            Manage inventory →
          </a>
        </section>
      </div>
      <section className="mt-4 overflow-hidden rounded-lg border border-[#e2e5eb] bg-white">
        <div className="flex items-center justify-between p-4">
          <div>
            <h2 className="text-xs font-semibold">Recent orders</h2>
            <p className="mt-1 text-base text-[#8992a4]">
              A live look at the latest activity
            </p>
          </div>
          <a
            href="/admin/orders"
            className="rounded border border-[#e2e5eb] px-3 py-2 text-base"
          >
            View all orders
          </a>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full min-w-[650px] text-left text-base">
            <thead className="bg-[#f7f8fa] text-[#7e8798]">
              <tr>
                <th className="px-4 py-3 font-medium">Order</th>
                <th className="px-4 py-3 font-medium">Customer</th>
                <th className="px-4 py-3 font-medium">Items</th>
                <th className="px-4 py-3 font-medium">Total</th>
                <th className="px-4 py-3 font-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              {["#FR-1842", "#FR-1841", "#FR-1840", "#FR-1839"].map(
                (order, index) => (
                  <tr className="border-t border-[#edf0f3]" key={order}>
                    <td className="px-4 py-3 font-medium text-[#5146dc]">
                      {order}
                    </td>
                    <td className="px-4 py-3">
                      {index < 2
                        ? "Alex Chen"
                        : index === 2
                          ? "Olivia Rhyne"
                          : "Phoenix Baker"}
                    </td>
                    <td className="px-4 py-3 text-[#667187]">
                      {
                        [
                          "Studio Wireless Headphones",
                          "Arc Desk Lamp + Mono Keyboard",
                          "Connect USB-C Hub",
                          "Everyday Desk Mat",
                        ][index]
                      }
                    </td>
                    <td className="px-4 py-3">
                      ${["160.92", "235.44", "71.72", "92.24"][index]}
                    </td>
                    <td className="px-4 py-3">
                      <span
                        className={`rounded-full px-2 py-1 text-base ${index % 2 ? "bg-[#eef7ff] text-[#2876c5]" : "bg-[#fff8e6] text-[#b47616]"}`}
                      >
                        ● {index % 2 ? "Processing" : "Pending"}
                      </span>
                    </td>
                  </tr>
                ),
              )}
            </tbody>
          </table>
        </div>
      </section>
      <div className="mt-4 flex items-center gap-1 text-base text-[#4c986a]">
        <Users size={11} /> Live updates connected
      </div>
    </main>
  );
}
