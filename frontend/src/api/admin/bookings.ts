import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { AdminBooking, PaginatedResponse } from '@/types'

export interface BookingFilters {
  court_id?: number
  date?: string
  status?: string
  payment_method?: string
  phone?: string
  page?: number
}

export function useAdminBookings(filters: BookingFilters) {
  return useQuery({
    queryKey: ['admin', 'bookings', filters],
    queryFn: async () => {
      const { data } = await apiClient.get<PaginatedResponse<AdminBooking>>('/admin/bookings', {
        params: filters,
      })
      return data
    },
    placeholderData: (previousData) => previousData,
  })
}

export function useMarkBookingPaid() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const { data } = await apiClient.patch<{ data: AdminBooking }>(`/admin/bookings/${id}/mark-paid`)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'bookings'] }),
  })
}

export function useCancelBooking() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, admin_notes }: { id: number; admin_notes?: string }) => {
      const { data } = await apiClient.patch<{ data: AdminBooking }>(`/admin/bookings/${id}/cancel`, { admin_notes })
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'bookings'] }),
  })
}
