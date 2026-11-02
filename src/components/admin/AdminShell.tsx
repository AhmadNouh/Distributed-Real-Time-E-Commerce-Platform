import {
  Bell,
  ChevronDown,
  ExternalLink,
  LayoutDashboard,
  Package,
  Settings,
  ShoppingBag,
  Warehouse,
} from "lucide-react";
import { NavLink, Outlet, Link } from "react-router";

const links = [
  { to: "/admin", label: "Overview", icon: LayoutDashboard, end: true },
  { to: "/admin/products", label: "Products", icon: Package },
  { to: "/admin/orders", label: "Orders", icon: ShoppingBag },
  { to: "/admin/inventory", label: "Inventory", icon: Warehouse },
];

export function AdminShell() {
  return (
    <div className="min-h-screen bg-[#f7f8fa] text-[#20283a] lg:flex">
      <aside className="hidden w-[220px] shrink-0 border-r border-[#e3e6ec] bg-white lg:flex lg:flex-col">
        <Link
          to="/admin"
          className="flex items-center gap-2 px-5 py-5 text-[34px] font-bold tracking-[-.05em]"
        >
          <span className="grid h-8 w-8 place-items-center rounded-md bg-[#5146dc] text-sm text-white">
            ⌁
          </span>{" "}
          forma{" "}
          <small className="rounded bg-[#eeedff] px-1.5 py-0.5 text-xs font-semibold text-[#5146dc]">
            ADMIN
          </small>
        </Link>
        <div className="mx-4 rounded-md border border-[#e5e8ed] p-3 text-sm">
          <b>Forma Store</b>
          <span className="mt-1 block text-[#8b94a5]">
            Production workspace
          </span>
          <ChevronDown size={13} className="float-right -mt-3 text-[#7d8698]" />
        </div>
        <p className="px-5 pb-3 pt-8 text-xs font-semibold uppercase tracking-wide text-[#9aa2b1]">
          Manage
        </p>
        <nav className="space-y-1 px-3">
          {links.map(({ to, label, icon: Icon, end }) => (
            <NavLink
              end={end}
              key={to}
              to={to}
              className={({ isActive }) =>
                `flex items-center gap-3 rounded-md px-3 py-3 text-sm ${isActive ? "bg-[#eeedff] font-semibold text-[#5146dc]" : "text-[#637087] hover:bg-[#f5f5fa]"}`
              }
            >
              <Icon size={16} />
              {label}
              {label === "Orders" && (
                <span className="ml-auto rounded bg-[#f0f1f5] px-2 py-0.5 text-base">
                  18
                </span>
              )}
            </NavLink>
          ))}
        </nav>
        <div className="mt-auto px-2 pb-4">
          <div className="mb-4 rounded-md bg-[#f7f8fa] p-3 text-base text-[#4c986a]">
            ● All systems operational
            <span className="mt-1 block text-[#9aa2b1]">
              Last checked just now
            </span>
          </div>
          <a
            href="#settings"
            className="flex items-center gap-3 px-3 py-3 text-base text-[#637087]"
          >
            <Settings size={16} />
            Settings
          </a>
          <div className="mt-3 flex items-center gap-3 border-t border-[#e5e8ed] pt-4 text-base">
            <span className="grid h-8 w-8 place-items-center rounded-full bg-[#eeedff] text-[#5146dc]">
              JL
            </span>
            <span>
              <b>Jordan Lee</b>
              <small className="block text-[#8b94a5]">
                Store administrator
              </small>
            </span>
          </div>
        </div>
      </aside>
      <section className="min-w-0 flex-1">
        <header className="flex h-16 items-center justify-between border-b border-[#e3e6ec] bg-white px-7 text-sm text-[#7d8698]">
          <span>
            Workspace　›　<b className="text-[#273044]">Overview</b>
          </span>
          <div className="flex items-center gap-5">
            <Link to="/store" className="hidden items-center gap-1 sm:flex">
              View storefront <ExternalLink size={13} />
            </Link>
            <Bell size={17} />
            <span className="grid h-8 w-8 place-items-center rounded-full bg-[#eeedff] text-[#5146dc]">
              JL
            </span>
          </div>
        </header>
        <Outlet />
      </section>
    </div>
  );
}
