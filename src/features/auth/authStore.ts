import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import { normalizeUser, type User } from './auth.types'
import { authApi } from './authApi'

type AuthStore = {
  user: User | null
  token: string | null
  setUser: (user: User | null) => void
  setToken: (token: string | null) => void
  setAuth: (user: User, token: string) => void
  fetchUser: () => Promise<void>
  logout: () => void
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      setUser: (user) => set({ user }),
      setToken: (token) => set({ token }),
      setAuth: (user, token) => set({ user, token }),
      
      fetchUser: async () => {
        const token = get().token
        if (!token) return

        try {
          const response = await authApi.me()
          const payload = response.data as any
          const userData = payload?.user ?? payload?.data?.user ?? payload?.data ?? payload
          if (userData?.email) {
            set({ user: normalizeUser(userData, token) })
          }
        } catch (error) {
          console.error('Failed to fetch current user:', error)
        }
      },

      logout: () => {
        set({ user: null, token: null })
        localStorage.removeItem('auth-storage')
      }
    }),
    {
      name: 'auth-storage',
    }
  )
)