import { ArrowLeft, ImagePlus, Plus } from "lucide-react";
import { Link } from "react-router";
import { AdminPageHeader } from "../../components/admin/AdminPageHeader";
import { featuredProducts } from "../../features/products/productData";

export function AdminProductCreatePage() {
  const preview = featuredProducts[0];
  return (
    <main className="mx-auto max-w-[1120px] p-5 sm:p-7">
      <Link
        to="/admin/products"
        className="mb-4 flex items-center gap-1 text-base text-[#7e8798]"
      >
        <ArrowLeft size={11} /> Back to products
      </Link>
      <AdminPageHeader
        title="Add New Product"
        description="Give your next everyday essential a place in the store."
      />
      <div className="grid gap-4 lg:grid-cols-[1fr_260px]">
        <div className="space-y-4">
          <section className="rounded-lg border border-[#e2e5eb] bg-white p-4">
            <h2 className="text-xs font-semibold">Product information</h2>
            <p className="mt-1 text-base text-[#8992a4]">
              The details customers will see in your storefront.
            </p>
            <div className="mt-6 grid gap-4">
              <label className="text-base font-medium">
                Product name *
                <input
                  className="mt-2 h-10 w-full rounded border border-[#e1e4eb] p-3 text-xs"
                  defaultValue="Orbit Laptop Stand"
                />
              </label>
              <label className="text-base font-medium">
                Description *
                <textarea
                  className="mt-2 h-20 w-full rounded border border-[#e1e4eb] p-3 text-xs"
                  defaultValue="A little lift for a better workday. Precision-crafted aluminum raises your laptop to a comfortable height."
                />
              </label>
              <div className="grid gap-3 sm:grid-cols-2">
                <label className="text-base font-medium">
                  Category *
                  <select className="mt-2 h-10 w-full rounded border border-[#e1e4eb] px-3 text-xs">
                    <option>Desk essentials</option>
                  </select>
                </label>
                <label className="text-base font-medium">
                  SKU *
                  <input
                    className="mt-2 h-10 w-full rounded border border-[#e1e4eb] px-3 text-xs"
                    defaultValue="FR-DS-007"
                  />
                </label>
              </div>
            </div>
          </section>
          <section className="rounded-lg border border-[#e2e5eb] bg-white p-4">
            <h2 className="text-xs font-semibold">Product image</h2>
            <p className="mt-1 text-base text-[#8992a4]">
              A clear, well-lit image helps customers choose with confidence.
            </p>
            <div className="mt-5 grid place-items-center rounded border border-dashed border-[#766cf0] bg-[#fbfaff] py-7 text-center text-base text-[#5146dc]">
              <ImagePlus size={18} />
              <p className="mt-2">Click to upload or drag and drop</p>
              <small className="text-[#8992a4]">
                PNG, JPG or WEBP · Up to 5 MB
              </small>
            </div>
          </section>
          <section className="rounded-lg border border-[#e2e5eb] bg-white p-4">
            <h2 className="text-xs font-semibold">Pricing & inventory</h2>
            <div className="mt-5 grid gap-3 sm:grid-cols-2">
              <label className="text-base">
                Price (USD) *
                <input
                  className="mt-2 h-10 w-full rounded border border-[#e1e4eb] px-3 text-xs"
                  defaultValue="69.00"
                />
              </label>
              <label className="text-base">
                Initial stock *
                <input
                  className="mt-2 h-10 w-full rounded border border-[#e1e4eb] px-3 text-xs"
                  defaultValue="20"
                />
              </label>
            </div>
          </section>
        </div>
        <aside className="space-y-4">
          <section className="overflow-hidden rounded-lg border border-[#e2e5eb] bg-white">
            <h2 className="p-4 text-xs font-semibold">Storefront preview</h2>
            <img
              src={preview.image}
              alt=""
              className="h-40 w-full object-cover"
            />
            <div className="p-4">
              <span className="text-base text-[#8992a4]">Desk essentials</span>
              <h3 className="mt-2 text-sm font-semibold">Orbit Laptop Stand</h3>
              <p className="text-base text-[#8992a4]">Silver</p>
              <b className="mt-3 block">$69.00</b>
            </div>
          </section>
          <section className="rounded-lg border border-[#e2e5eb] bg-white p-4">
            <h2 className="text-xs font-semibold">Ready for the shelf?</h2>
            <p className="mt-4 text-base text-[#26834d]">
              ✓ Product information added
            </p>
            <p className="mt-3 text-base text-[#26834d]">✓ Image uploaded</p>
            <button className="mt-5 flex w-full items-center justify-center gap-2 rounded bg-[#5146dc] py-2.5 text-base text-white">
              <Plus size={11} /> Create product
            </button>
            <button className="mt-2 w-full rounded border border-[#e1e4eb] py-2 text-base">
              Cancel
            </button>
          </section>
        </aside>
      </div>
    </main>
  );
}
