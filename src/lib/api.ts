import axios from 'axios'
import { useAuthStore } from '../features/auth/authStore'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/v1',
  headers: { 
    'Accept': 'application/json',
  },
})

function getToken(): string | null {
  try {
    const state = useAuthStore.getState()
    if (state?.token) {
      return state.token
    }
  } catch {
    // ignore
  }

  try {
    const storageStr = localStorage.getItem('auth-storage')
    if (storageStr) {
      const storage = JSON.parse(storageStr)
      const token = storage?.state?.token || storage?.token
      if (token) return token
    }
  } catch {
    // ignore
  }

  try {
    const token = localStorage.getItem('token') || localStorage.getItem('access_token') || localStorage.getItem('auth_token')
    if (token) return token
  } catch {
    // ignore
  }

  return null
}

api.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers = config.headers ?? {}
    config.headers.Authorization = `Bearer ${token}`
  }

  if (config.data instanceof FormData) {
    if (config.headers && 'Content-Type' in config.headers) {
      delete (config.headers as any)['Content-Type']
    }
  } else if (config.headers && !(config.headers as any)['Content-Type']) {
    (config.headers as any)['Content-Type'] = 'application/json'
  }

  return config
})

export const productsApi = {
  list: () => api.get('/products'),
  create: (product: unknown) => api.post('/products', product),
}