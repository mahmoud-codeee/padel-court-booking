import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { ClientBooking, CustomerInfo, PaymentMethod, RequestedSlot } from '@/types'

interface CreateBookingPayload {
  slots: RequestedSlot[]
  customer: CustomerInfo
  payment_method: PaymentMethod
}

export function useCreateBooking() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (payload: CreateBookingPayload) => {
      const { data } = await apiClient.post<{ data: ClientBooking }>('/bookings', payload)
      return data.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['availability'] })
    },
  })
}

export function useBooking(reference: string | undefined) {
  return useQuery({
    queryKey: ['booking', reference],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: ClientBooking }>(`/bookings/${reference}`)
      return data.data
    },
    enabled: !!reference,
  })
}

export function usePaymentStatus(reference: string | undefined, enabled: boolean) {
  return useQuery({
    queryKey: ['booking-payment-status', reference],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: { status: string; payment_status: string } }>(
        `/bookings/${reference}/payment-status`,
      )
      return data.data
    },
    enabled: !!reference && enabled,
    refetchInterval: (query) => {
      const status = query.state.data?.payment_status
      if (status === 'paid' || status === 'failed') return false
      return 2000
    },
  })
}
