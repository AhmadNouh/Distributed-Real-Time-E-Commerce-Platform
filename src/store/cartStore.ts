import { create } from 'zustand'
import type { Product } from '../features/products/product.types'

export type CartItem = Product & { quantity: number }

type CartStore = {
  cart: CartItem[]
  addToCart: (product: Product, quantity?: number) => void
  removeFromCart: (id: string) => void
  clearCart: () => void
}

export const useCartStore = create<CartStore>((set) => ({
  cart: [],
  addToCart: (product, quantity = 1) =>
    set((state) => {
      const existing = state.cart.find((item) => item.id === product.id)
      return {
        cart: existing
          ? state.cart.map((item) =>
              item.id === product.id
                ? { ...item, quantity: item.quantity + 1 }
                : item,
            )
          : [...state.cart, { ...product, quantity }],
      }
    }),
  removeFromCart: (id) =>
    set((state) => ({ cart: state.cart.filter((item) => item.id !== id) })),
  clearCart: () => set({ cart: [] }),
}))
