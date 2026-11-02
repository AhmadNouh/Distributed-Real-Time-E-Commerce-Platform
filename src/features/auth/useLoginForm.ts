import axios from 'axios'
import { useState } from 'react'
import { useNavigate } from 'react-router'
import { normalizeUser } from './auth.types'
import { useAuthStore } from './authStore'
import { authApi } from './authApi'

export function useLoginForm() {
  const navigate = useNavigate()
  
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function submit() {
    setError(null)
    setIsSubmitting(true)

    try {
      const response = await authApi.login({ email, password })
      const data = response.data as any

      const payload = data?.data ?? data
      const token = payload.token || payload.access_token || payload.accessToken || data.token || data.access_token
      let rawUser = payload.user || data.user || payload.account || data.account
      let user = rawUser ? normalizeUser(rawUser, token) : null

      const authState = useAuthStore.getState()

      if (token && user) {
        authState.setAuth(user, token)
      } else {
        if (token) authState.setToken(token)
        if (user) authState.setUser(user)
      }

      // جلب بيانات المستخدم للتأكد من role
      if (token) {
        try {
          await authState.fetchUser()
          user = useAuthStore.getState().user || user
        } catch {
          // تجاهل
        }
      }

      // توجيه حسب الدور
      if (user?.role === 'admin') {
        navigate('/admin', { replace: true })
      } else {
        navigate('/store', { replace: true })
      }

    } catch (requestError) {
      console.error('Login error:', requestError)
      setError(
        axios.isAxiosError(requestError)
          ? requestError.response?.data?.message ?? 'Unable to sign in. Please check your credentials.'
          : 'Unable to sign in. Please try again.',
      )
    } finally {
      setIsSubmitting(false)
    }
  }

  return {
    email,
    password,
    showPassword,
    isSubmitting,
    error,
    setEmail,
    setPassword,
    setShowPassword,
    submit,
  }
}