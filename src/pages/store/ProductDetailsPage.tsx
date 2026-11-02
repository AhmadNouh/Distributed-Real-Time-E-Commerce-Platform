import { useState } from "react";
import {
  AlertTriangle,
  ArrowLeft,
  Check,
  Minus,
  Plus,
  ShoppingBag,
} from "lucide-react";
import { Link, useParams } from "react-router";
import { useCartStore } from "../../store/cartStore";
import { useProductDetails } from "../../features/products/useProductDetails"; // 👈 استدعاء الـ Hook الجديد

export function ProductDetailsPage() {
  const { id } = useParams<{ id: string }>();
  const { product, isLoading, error } = useProductDetails(id);
  const addToCart = useCartStore((state) => state.addToCart);
  
  const [quantity, setQuantity] = useState<number>(1);

  if (isLoading) {
    return <div className="py-20 text-center text-sm text-gray-500">Loading product details...</div>;
  }

  if (error || !product) {
    return (
      <div className="py-20 text-center">
        <p className="text-sm text-red-500 mb-4">{error || 'Product not found.'}</p>
        <Link to="/store" className="text-sm font-medium text-[#5146dc] underline">
          ← Back to store
        </Link>
      </div>
    );
  }

  const stockCount = Number(product.stock ?? 0);
  const unavailable = stockCount === 0;
  const productPrice = Number(product.price ?? 0);
  const categoryDisplay = product.categoryName || (product as any).category?.name || 'General';

  return (
    <main className="mx-auto max-w-[1160px] px-5 py-5">
      <div className="mb-5 text-xs text-[#7d8698] sm:text-sm">
        <Link to="/store">Shop</Link> › {categoryDisplay} › 
        <span className="text-[#273044]">{product.name}</span>
      </div>

      {unavailable && (
        <div className="mb-4 rounded-md border border-[#f1caca] bg-[#fff3f3] p-3 text-sm text-[#c84848]">
          <b className="flex items-center gap-2">
            <AlertTriangle size={14} />
            Insufficient Stock
          </b>
          <p className="mt-1">
            This product is currently unavailable. Your order has not been
            created and you have not been charged.
          </p>
        </div>
      )}

      <div className="grid gap-8 lg:grid-cols-[1fr_1.2fr_250px]">
        <div>
          <img
            src={product.image || 'https://via.placeholder.com/400'}
            alt={product.name}
            className="h-[350px] w-full rounded-lg object-cover"
          />

          <div className="mt-3 flex gap-2">
            <img
              src={product.image || 'https://via.placeholder.com/100'}
              alt=""
              className="h-14 w-14 rounded border-2 border-[#5146dc] object-cover"
            />
          </div>

          <p className="mt-4 text-sm leading-5 text-[#7d8698]">
            ♧ Made to last. Backed by a 2-year warranty.
          </p>
        </div>

        <section>
          <p className="text-xs font-medium uppercase text-[#5146dc] sm:text-sm">
            {categoryDisplay} / ID-{product.id}
          </p>

          <h1 className="mt-2 text-2xl font-semibold leading-tight tracking-tight sm:text-3xl">
            {product.name}
          </h1>

          <p className="mt-2 text-sm text-[#b47616]">
            ★★★★★
            <span className="ml-2 text-[#7d8698]">4.9 (128 reviews)</span>
          </p>

          <strong className="mt-3 block text-2xl">
            ${productPrice.toFixed(2)}
          </strong>

          <p className="mt-5 max-w-md text-sm leading-5 text-[#7d8698]">
            {product.description}
          </p>

          <hr className="my-5 border-[#e5e8ed]" />

          <p
            className={`mt-4 text-sm ${
              unavailable ? "text-[#c84848]" : "text-[#26834d]"
            }`}
          >
            ● {unavailable ? "Out of stock" : `${stockCount} available`}
          </p>

          <div className="mt-5 flex items-center gap-3 text-sm">
            <span>Quantity</span>
            <div className="flex items-center gap-4 rounded border border-[#e1e4eb] px-3 py-2">
              <button 
                type="button" 
                aria-label="Decrease quantity"
                onClick={() => setQuantity((q) => Math.max(1, q - 1))}
              >
                <Minus size={12} />
              </button>
              <span>{quantity}</span>
              <button 
                type="button" 
                aria-label="Increase quantity"
                onClick={() => setQuantity((q) => Math.min(stockCount || 99, q + 1))}
              >
                <Plus size={12} />
              </button>
            </div>
          </div>

          <ul className="mt-6 space-y-3 border-t border-[#e5e8ed] pt-5 text-sm leading-5 text-[#7d8698]">
            <li>
              <Check size={14} className="mr-2 inline text-[#5146dc]" />
              Fast and secure shipping
            </li>
            <li>
              <Check size={14} className="mr-2 inline text-[#5146dc]" />
              Verified high quality backend data
            </li>
          </ul>
        </section>

        <aside className="h-fit rounded-lg border border-[#e2e5eb] bg-white p-4 shadow-sm">
          <h2 className="text-sm font-semibold">Order summary</h2>

          <div className="mt-5 flex gap-3 border-b border-[#edf0f3] pb-4">
            <img
              src={product.image || 'https://via.placeholder.com/100'}
              alt=""
              className="h-10 w-10 rounded object-cover"
            />
            <div className="min-w-0">
              <p className="text-sm font-medium">{product.name}</p>
              <span className="text-xs leading-5 text-[#8992a4]">
                {product.description} · Qty {quantity}
              </span>
            </div>
          </div>

          <div className="mt-4 flex justify-between gap-2 text-sm text-[#7d8698]">
            <span>Subtotal</span>
            <span>${(productPrice * quantity).toFixed(2)}</span>
          </div>

          <div className="mt-2 flex justify-between gap-2 text-sm text-[#7d8698]">
            <span>Shipping</span>
            <span className="text-[#26834d]">Free</span>
          </div>

          <div className="my-4 border-t border-[#edf0f3]" />

          <div className="flex justify-between text-sm font-semibold">
            <span>Total</span>
            <span>${(productPrice * quantity).toFixed(2)}</span>
          </div>

          <button
            type="button"
            disabled={unavailable}
            onClick={() => addToCart(product, quantity)}
            className="mt-5 flex w-full items-center justify-center gap-2 rounded bg-[#5146dc] py-3 text-sm text-white disabled:bg-[#eef0f4] disabled:text-[#a0a8b5]"
          >
            <ShoppingBag size={14} />
            {unavailable ? "Ordering unavailable" : "Add to Cart"}
          </button>

          <Link
            to="/store"
            className="mt-2 flex w-full items-center justify-center gap-2 rounded border border-[#e1e4eb] py-2.5 text-sm"
          >
            <ArrowLeft size={12} />
            Continue browsing
          </Link>
        </aside>
      </div>
    </main>
  );
}