import { Navigate, Outlet, useLocation } from 'react-router'
import { useAuthStore } from '../../features/auth/authStore'
import { useEffect, useState } from 'react'

export function AdminGuard() {
  const location = useLocation()
  const token = useAuthStore((state) => state.token)
  const user = useAuthStore((state) => state.user)
  const fetchUser = useAuthStore((state) => state.fetchUser)
  const [checking, setChecking] = useState(!!token && !user)

  useEffect(() => {
    const check = async () => {
      if (token && !user) {
        try {
          await fetchUser()
        } catch {
          // ignore
        }
      }
      setChecking(false)
    }
    check()
  }, [token, user, fetchUser])

  if (checking) {
    return <div className="p-6">Loading...</div>
  }

  if (!user || !token) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />
  }

  if (user.role == 'admin') {
    return <Navigate to="/store" replace />
  }

  return <Outlet />
}