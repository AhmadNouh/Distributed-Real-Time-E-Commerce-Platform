import { Outlet } from "react-router";
import { StoreFooter } from "../components/store/StoreFooter";
import { StoreHeader } from "../components/store/StoreHeader";

export function StoreLayout() {
  return (
    <div className="min-h-screen bg-[#f7f8fa]">
      <StoreHeader />
      <Outlet />
      <StoreFooter />
    </div>
  );
}
