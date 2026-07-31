import { useCallback, useState } from 'react'
import { DateTabs } from '@/components/client/DateTabs'
import { SlotButton } from '@/components/client/SlotButton'
import { CartSummaryBar } from '@/components/client/CartSummaryBar'
import { Skeleton } from '@/components/ui/skeleton'
import { useAvailability } from '@/api/client/availability'
import { useBookingCartStore } from '@/stores/bookingCartStore'
import { todayISODate } from '@/lib/utils'

export default function BookingFlowPage() {
  const [selectedDate, setSelectedDate] = useState(todayISODate())
  const { data: slots, isLoading } = useAvailability(selectedDate)
  const items = useBookingCartStore((s) => s.items)
  const toggleItem = useBookingCartStore((s) => s.toggleItem)

  const handleToggle = useCallback(
    (hour: number) => toggleItem({ date: selectedDate, hour }),
    [selectedDate, toggleItem],
  )

  const selectedHoursForDate = new Set(
    items.filter((i) => i.date === selectedDate).map((i) => i.hour),
  )

  return (
    <div className="min-h-screen pb-28">
      <header className="border-b bg-card">
        <div className="mx-auto max-w-3xl px-4 py-6">
          <h1 className="text-2xl font-semibold">Book a Padel Court</h1>
          <p className="text-sm text-muted-foreground">
            Pick a date and time — no account needed.
          </p>
        </div>
      </header>

      <main className="mx-auto max-w-3xl px-4 py-6">
        <DateTabs selectedDate={selectedDate} onSelect={setSelectedDate} />

        <div className="mt-6">
          {isLoading ? (
            <div className="grid grid-cols-3 gap-2 sm:grid-cols-4">
              {Array.from({ length: 12 }).map((_, i) => (
                <Skeleton key={i} className="h-11 w-full" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-3 gap-2 sm:grid-cols-4">
              {slots?.map((slot) => (
                <SlotButton
                  key={slot.hour}
                  hour={slot.hour}
                  available={slot.available}
                  selected={selectedHoursForDate.has(slot.hour)}
                  onToggle={handleToggle}
                />
              ))}
            </div>
          )}
          {!isLoading && slots?.every((s) => !s.available) && (
            <p className="mt-6 text-center text-muted-foreground">
              No slots available on this date. Try another day.
            </p>
          )}
        </div>
      </main>

      <CartSummaryBar />
    </div>
  )
}
