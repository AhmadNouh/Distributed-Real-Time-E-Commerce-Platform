import { ArrowLeft, Minus, Plus, Trash2 } from "lucide-react";
import { Link } from "react-router";
import { useCartStore } from "../../store/cartStore";

export function CartPage() {
  const cart = useCartStore((state) => state.cart);
  const removeFromCart = useCartStore((state) => state.removeFromCart);
  const subtotal = cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0,
  );

  return (
    <main className="mx-auto min-h-[calc(100vh-112px)] max-w-[1000px] px-5 py-10">
      <div className="flex items-end justify-between">
        <div>
          <p className="text-[20px] uppercase tracking-wide text-[#5146dc]">
            Your selection
          </p>
          <h1 className="mt-1 text-2xl font-semibold text-[#20283a]">
            Shopping cart
          </h1>
        </div>
        <Link to="/store" className="text-[20px] text-[#5146dc]">
          Continue shopping →
        </Link>
      </div>
      {cart.length === 0 ? (
        <div className="mt-12 rounded-lg border border-dashed border-[#dfe2e9] py-20 text-center text-sm text-[#7d8698]">
          Your cart is empty.
        </div>
      ) : (
        <div className="mt-8 grid gap-6 md:grid-cols-[1fr_290px]">
          <div className="rounded-lg border border-[#e2e5eb] bg-white">
            {cart.map((item) => (
              <div
                key={item.id}
                className="flex items-center gap-4 border-b border-[#edf0f3] p-4 last:border-0"
              >
                <img
                  src={item.image}
                  alt={item.name}
                  className="h-16 w-16 rounded object-cover"
                />
                <div className="min-w-0 flex-1">
                  <h2 className="truncate text-xs font-semibold text-[#273044]">
                    {item.name}
                  </h2>
                  <p className="mt-1 text-[20px] text-[#8b94a5]">
                    ${item.price.toFixed(2)}
                  </p>
                  <div className="mt-2 flex items-center gap-2 text-[20px] text-[#657085]">
                    <button>
                      <Minus size={12} />
                    </button>
                    {item.quantity}
                    <button>
                      <Plus size={12} />
                    </button>
                  </div>
                </div>
                <button
                  onClick={() => removeFromCart(item.id)}
                  className="text-[#a3aab7] hover:text-red-500"
                  aria-label={`Remove ${item.name}`}
                >
                  <Trash2 size={15} />
                </button>
              </div>
            ))}
          </div>
          <aside className="h-fit rounded-lg bg-[#eeedff] p-5">
            <h2 className="text-sm font-semibold text-[#273044]">
              Order summary
            </h2>
            <div className="mt-5 flex justify-between text-xs text-[#737d90]">
              <span>Subtotal</span>
              <span>${subtotal.toFixed(2)}</span>
            </div>
            <div className="mt-3 flex justify-between text-xs text-[#737d90]">
              <span>Shipping</span>
              <span>Free</span>
            </div>
            <div className="my-5 border-t border-[#dcdaf4]" />
            <div className="flex justify-between text-sm font-semibold text-[#273044]">
              <span>Total</span>
              <span>${subtotal.toFixed(2)}</span>
            </div>
            <button className="mt-5 flex w-full items-center justify-center gap-2 rounded-md bg-[#5146dc] py-3 text-xs font-medium text-white">
              Checkout <ArrowLeft size={13} />
            </button>
          </aside>
        </div>
      )}
    </main>
  );
}
