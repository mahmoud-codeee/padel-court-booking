import { useMutation } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import { useAdminAuthStore } from '@/stores/adminAuthStore'
import type { AdminInfo } from '@/types'

export function useAdminLogin() {
  const setAuth = useAdminAuthStore((s) => s.setAuth)

  return useMutation({
    mutationFn: async (payload: { email: string; password: string }) => {
      const { data } = await apiClient.post<{ token: string; admin: AdminInfo }>('/admin/login', payload)
      return data
    },
    onSuccess: (data) => setAuth(data.token, data.admin),
  })
}

export function useAdminLogout() {
  const logout = useAdminAuthStore((s) => s.logout)

  return useMutation({
    mutationFn: async () => {
      await apiClient.post('/admin/logout')
    },
    onSettled: () => logout(),
  })
}
