import { useQuery } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { AvailabilitySlot } from '@/types'

export function useAvailability(date: string) {
  return useQuery({
    queryKey: ['availability', date],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: AvailabilitySlot[] }>('/availability', {
        params: { date },
      })
      return data.data
    },
    enabled: !!date,
    staleTime: 15_000,
  })
}
