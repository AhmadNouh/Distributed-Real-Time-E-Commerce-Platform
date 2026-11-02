import axios from 'axios'
import { useState } from 'react'
import { useNavigate } from 'react-router'
import { authApi } from './authApi'

export function useSignupForm() {
  const navigate = useNavigate()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function submit() {
    if (password !== passwordConfirmation) {
      setError('Passwords do not match.')
      return
    }

    setError(null)
    setIsSubmitting(true)

    try {
      // 1. إرسال طلب إنشاء الحساب إلى لارافيل
      await authApi.signup({ 
        name, 
        email, 
        password, 
        password_confirmation: passwordConfirmation 
      })

      // 2. نجاح التسجيل: لا نقوم بتسجيل الدخول تلقائياً، بل نوجه المستخدم لصفحة تسجيل الدخول
      navigate('/login', { replace: true })
      
    } catch (requestError) {
      setError(
        axios.isAxiosError(requestError)
          ? requestError.response?.data?.message ?? 'Unable to create your account.'
          : 'Unable to create your account. Please try again.',
      )
    } finally {
      setIsSubmitting(false)
    }
  }

  return {
    name,
    email,
    password,
    passwordConfirmation,
    showPassword,
    showPasswordConfirmation,
    isSubmitting,
    error,
    setName,
    setEmail,
    setPassword,
    setPasswordConfirmation,
    setShowPassword,
    setShowPasswordConfirmation,
    submit,
  }
}