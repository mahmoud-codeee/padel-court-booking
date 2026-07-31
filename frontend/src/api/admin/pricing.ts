import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { DiscountTier, PricingSetting } from '@/types'

export function useAdminPricingSettings() {
  return useQuery({
    queryKey: ['admin', 'pricing-settings'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: PricingSetting }>('/admin/pricing-settings')
      return data.data
    },
  })
}

export function useUpdatePricingSettings() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (payload: { base_price_per_hour: number }) => {
      const { data } = await apiClient.put<{ data: PricingSetting }>('/admin/pricing-settings', payload)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'pricing-settings'] }),
  })
}

export function useAdminDiscountTiers() {
  return useQuery({
    queryKey: ['admin', 'discount-tiers'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: DiscountTier[] }>('/admin/discount-tiers')
      return data.data
    },
  })
}

interface TierPayload {
  min_hours: number
  max_hours: number | null
  price_per_hour: number
  is_active?: boolean
}

export function useCreateDiscountTier() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (payload: TierPayload) => {
      const { data } = await apiClient.post<{ data: DiscountTier }>('/admin/discount-tiers', payload)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'discount-tiers'] }),
  })
}

export function useUpdateDiscountTier() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...payload }: TierPayload & { id: number }) => {
      const { data } = await apiClient.put<{ data: DiscountTier }>(`/admin/discount-tiers/${id}`, payload)
      return data.data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'discount-tiers'] }),
  })
}

export function useDeleteDiscountTier() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      await apiClient.delete(`/admin/discount-tiers/${id}`)
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['admin', 'discount-tiers'] }),
  })
}
