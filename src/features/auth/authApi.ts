import { api } from '../../lib/api'
import type { LoginCredentials, SignupCredentials, User } from './auth.types'

export interface LoginResponse {
  user: User;
  token?: string;        // 👈 أضف هذه
  access_token?: string; // 👈 أو هذه حسب ما يرسله لارافيل
}

export const authApi = {
  login: (credentials: LoginCredentials) =>
    api.post<LoginResponse>('/auth/login', credentials),
  signup: (credentials: SignupCredentials) =>
    api.post<LoginResponse>('/auth/register', credentials),
    logout: () => api.post('/auth/logout'),
    me: () => api.get<User>('/auth/me'),
}
