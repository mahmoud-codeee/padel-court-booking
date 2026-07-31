import { useMemo } from 'react'
import { Button } from '@/components/ui/button'
import type { RequestedSlot } from '@/types'
import { usePricing, calculateEstimatedTotal } from '@/api/client/pricing'
import { formatCurrency, formatDateLabel, formatHourLabel } from '@/lib/utils'

interface PriceBreakdownProps {
  items: RequestedSlot[]
  onRemove: (item: RequestedSlot) => void
}

export function PriceBreakdown({ items, onRemove }: PriceBreakdownProps) {
  const { data: pricing } = usePricing()

  const grouped = useMemo(() => {
    const byDate = new Map<string, number[]>()
    for (const item of items) {
      const hours = byDate.get(item.date) ?? []
      hours.push(item.hour)
      byDate.set(item.date, hours)
    }
    return Array.from(byDate.entries())
      .map(([date, hours]) => [date, hours.sort((a, b) => a - b)] as const)
      .sort(([a], [b]) => a.localeCompare(b))
  }, [items])

  const { pricePerHour, totalAmount } = useMemo(
    () => calculateEstimatedTotal(pricing, items.length),
    [pricing, items.length],
  )

  return (
    <div className="rounded-lg border bg-card">
      <div className="divide-y">
        {grouped.map(([date, hours]) => (
          <div key={date} className="flex items-start justify-between gap-4 p-4">
            <div>
              <p className="font-medium">{formatDateLabel(date)}</p>
              <div className="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm text-muted-foreground">
                {hours.map((hour) => (
                  <span key={hour} className="inline-flex items-center gap-1">
                    {formatHourLabel(hour)}
                    <button
                      type="button"
                      onClick={() => onRemove({ date, hour })}
                      className="text-muted-foreground/70 hover:text-destructive"
                      aria-label={`Remove ${formatHourLabel(hour)} on ${date}`}
                    >
                      ×
                    </button>
                  </span>
                ))}
              </div>
            </div>
          </div>
        ))}
      </div>
      {pricing && (
        <div className="flex items-center justify-between border-t p-4 text-sm">
          <span className="text-muted-foreground">
            {items.length} hour{items.length !== 1 ? 's' : ''} &times;{' '}
            {formatCurrency(pricePerHour, pricing.currency)}
          </span>
          <span className="text-base font-semibold">{formatCurrency(totalAmount, pricing.currency)}</span>
        </div>
      )}
    </div>
  )
}

interface RemoveAllProps {
  onClear: () => void
}

export function ClearCartButton({ onClear }: RemoveAllProps) {
  return (
    <Button type="button" variant="ghost" size="sm" onClick={onClear}>
      Clear all
    </Button>
  )
}
