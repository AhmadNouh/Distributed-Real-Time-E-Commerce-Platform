import { api } from '../../lib/api'

export interface Notification {
  id: number | string
  title?: string
  message?: string
  read_at?: string | null
  created_at?: string
}

export const notificationsApi = {
  list: () => api.get<Notification[]>('/notifications'),
  markAsRead: (id: number | string) => api.patch(`/notifications/${id}/read`),
}
