import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { Court, CourtWorkingHour } from '@/types'

export function useAdminCourts() {
  return useQuery({
    queryKey: ['admin', 'courts'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: Court[] }>('/admin/courts')
      return data.data
    },
  })
}

export function useCreateCourt() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { name: string; description?: string }) => {
      const { data } = await apiClient.post<{ data: Court }>('/admin/courts', payload)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'courts'] }),
  })
}

export function useUpdateCourt() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...payload }: { id: number; name?: string; description?: string; is_active?: boolean }) => {
      const { data } = await apiClient.put<{ data: Court }>(`/admin/courts/${id}`, payload)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'courts'] }),
  })
}

export function useDeleteCourt() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/courts/${id}`)
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'courts'] }),
  })
}

export function useUpdateWorkingHours() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ courtId, hours }: { courtId: number; hours: CourtWorkingHour[] }) => {
      const { data } = await apiClient.put<{ data: CourtWorkingHour[] }>(`/admin/courts/${courtId}/working-hours`, {
        hours,
      })
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'courts'] }),
  })
}
