import { featuredProducts } from "../../features/products/productData";

export function InventoryTable() {
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[700px] text-left text-base">
        <thead className="bg-[#f7f8fa] text-[#7e8798]">
          <tr>
            {[
              "Product",
              "On hand",
              "Reserved",
              "Available",
              "Low-stock at",
              "Status",
              "",
            ].map((head) => (
              <th className="px-5 py-4 font-medium" key={head}>
                {head}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {featuredProducts.map((product) => (
            <tr className="border-t border-[#edf0f3]" key={product.id}>
              <td className="flex items-center gap-3 px-5 py-4">
                <img
                  src={product.image}
                  className="h-10 w-10 rounded object-cover"
                  alt=""
                />
                <span>
                  <b>{product.name}</b>
                  <small className="mt-1 block text-[#8992a4]">
                    FR-{product.id.slice(0, 3).toUpperCase()}
                  </small>
                </span>
              </td>
              <td className="px-5 py-4">{product.stock + 3}</td>
              <td className="px-5 py-4 text-[#8992a4]">
                {product.stock ? 2 : 0}
              </td>
              <td
                className={`px-5 py-4 font-semibold ${product.stock < 5 ? "text-[#c84848]" : ""}`}
              >
                {product.stock}
              </td>
              <td className="px-5 py-4">10 units</td>
              <td className="px-5 py-4">
                <span
                  className={`rounded-full px-2.5 py-1 text-base ${product.stock === 0 ? "bg-[#fff0f0] text-[#c84848]" : product.stock < 5 ? "bg-[#fff8e6] text-[#b47616]" : "bg-[#eaf9f0] text-[#26834d]"}`}
                >
                  ●{" "}
                  {product.stock === 0
                    ? "Out of stock"
                    : product.stock < 5
                      ? "Low stock"
                      : "Healthy"}
                </span>
              </td>
              <td className="px-5 py-4">
                <button className="rounded border border-[#e1e4eb] px-3 py-1.5 text-base">
                  Adjust
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
