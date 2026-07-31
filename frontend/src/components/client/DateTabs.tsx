import { useMemo } from 'react'
import { Button } from '@/components/ui/button'
import { cn, addDaysISO, formatDateLabel, todayISODate } from '@/lib/utils'

interface DateTabsProps {
  selectedDate: string
  onSelect: (date: string) => void
  daysAhead?: number
}

export function DateTabs({ selectedDate, onSelect, daysAhead = 21 }: DateTabsProps) {
  const dates = useMemo(() => {
    const today = todayISODate()
    return Array.from({ length: daysAhead }, (_, i) => addDaysISO(today, i))
  }, [daysAhead])

  return (
    <div className="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
      {dates.map((date) => (
        <Button
          key={date}
          type="button"
          variant={date === selectedDate ? 'default' : 'secondary'}
          onClick={() => onSelect(date)}
          className={cn('shrink-0 whitespace-nowrap')}
        >
          {formatDateLabel(date)}
        </Button>
      ))}
    </div>
  )
}
