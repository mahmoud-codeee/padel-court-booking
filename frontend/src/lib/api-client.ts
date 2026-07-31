import axios from 'axios'
import { useAdminAuthStore } from '@/stores/adminAuthStore'

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json' },
})

apiClient.interceptors.request.use((config) => {
  const token = useAdminAuthStore.getState().token
  if (token && config.url?.startsWith('/admin')) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && window.location.pathname.startsWith('/admin')) {
      useAdminAuthStore.getState().logout()
    }
    return Promise.reject(error)
  },
)

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export function extractErrorMessage(error: unknown, fallback = 'Something went wrong.'): string {
  if (axios.isAxiosError(error)) {
    const data = error.response?.data as ApiError | undefined
    return data?.message ?? fallback
  }
  return fallback
}
