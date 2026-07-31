import { useMemo } from 'react'
import { useNavigate } from 'react-router-dom'
import { Button } from '@/components/ui/button'
import { useBookingCartStore } from '@/stores/bookingCartStore'
import { usePricing, calculateEstimatedTotal } from '@/api/client/pricing'
import { formatCurrency } from '@/lib/utils'

export function CartSummaryBar() {
  const items = useBookingCartStore((s) => s.items)
  const navigate = useNavigate()
  const { data: pricing } = usePricing()

  const { totalAmount, pricePerHour } = useMemo(
    () => calculateEstimatedTotal(pricing, items.length),
    [pricing, items.length],
  )

  if (items.length === 0) return null

  return (
    <div className="fixed inset-x-0 bottom-0 z-40 border-t bg-card/95 backdrop-blur supports-backdrop-filter:bg-card/80">
      <div className="mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-3">
        <div className="min-w-0">
          <p className="font-medium">
            {items.length} hour{items.length !== 1 ? 's' : ''} selected
          </p>
          {pricing && (
            <p className="text-sm text-muted-foreground">
              {formatCurrency(pricePerHour, pricing.currency)}/hr &middot; est. total{' '}
              {formatCurrency(totalAmount, pricing.currency)}
            </p>
          )}
        </div>
        <Button size="lg" onClick={() => navigate('/checkout')}>
          Review &amp; Book
        </Button>
      </div>
    </div>
  )
}
