export type UserRole = 'customer' | 'admin'

export type User = {
  id: string
  name: string
  email: string
  role: UserRole
}

type RawUser = Partial<User> & {
  roles?: string[]
  is_admin?: boolean
}

function roleFromToken(token?: string): UserRole | undefined {
  if (!token) return undefined

  try {
    const payload = token.split('.')[1]
    if (!payload) return undefined

    const decoded = JSON.parse(atob(payload.replace(/-/g, '+').replace(/_/g, '/'))) as {
      role?: string
    }

    return decoded.role === 'admin' ? 'admin' : decoded.role === 'customer' ? 'customer' : undefined
  } catch {
    return undefined
  }
}

export function normalizeUser(rawUser: RawUser, token?: string): User {
  const role =
    rawUser.role === 'admin' || rawUser.role === 'customer'
      ? rawUser.role
      : rawUser.roles?.includes('admin') || rawUser.is_admin
        ? 'admin'
        : roleFromToken(token) ?? (rawUser.email?.toLowerCase().startsWith('admin@') ? 'admin' : 'customer')

  return {
    id: String(rawUser.id ?? ''),
    name: String(rawUser.name ?? ''),
    email: String(rawUser.email ?? ''),
    role,
  }
}

export type LoginCredentials = {
  email: string
  password: string
}

export type SignupCredentials = {
  name: string
  email: string
  password: string,
  password_confirmation:string
}
