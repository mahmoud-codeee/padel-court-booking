import { useQuery } from '@tanstack/react-query'
import { apiClient } from '@/lib/api-client'
import type { PricingInfo } from '@/types'

export function usePricing() {
  return useQuery({
    queryKey: ['pricing'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ data: PricingInfo }>('/pricing')
      return data.data
    },
    staleTime: 60_000,
  })
}

/** Client-side mirror of PricingService — cosmetic only, server always recomputes authoritatively. */
export function calculateEstimatedTotal(pricing: PricingInfo | undefined, totalHours: number) {
  if (!pricing || totalHours === 0) return { pricePerHour: pricing?.base_price_per_hour ?? 0, totalAmount: 0 }

  const tier = pricing.discount_tiers
    .filter((t) => t.is_active && t.min_hours <= totalHours && (t.max_hours === null || t.max_hours >= totalHours))
    .sort((a, b) => b.min_hours - a.min_hours)[0]

  const pricePerHour = tier?.price_per_hour ?? pricing.base_price_per_hour
  return { pricePerHour, totalAmount: Math.round(pricePerHour * totalHours * 100) / 100 }
}
