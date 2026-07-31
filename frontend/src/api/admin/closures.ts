import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { CourtClosure } from '@/types'

export function useAdminClosures() {
  return useQuery({
    queryKey: ['admin', 'closures'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: CourtClosure[] }>('/admin/court-closures')
      return data.data
    },
  })
}

interface CreateClosurePayload {
  all_courts: boolean
  court_ids?: number[]
  closure_date: string
  start_time?: string
  end_time?: string
  reason?: string
}

export function useCreateClosure() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (payload: CreateClosurePayload) => {
      const { data } = await apiClient.post<{ data: CourtClosure[] }>('/admin/court-closures', payload)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'closures'] }),
  })
}

export function useDeleteClosure() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/court-closures/${id}`)
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'closures'] }),
  })
}
