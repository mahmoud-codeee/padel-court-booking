import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Switch } from '@/components/ui/switch'
import type { CourtWorkingHour } from '@/types'

const DAY_LABELS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

interface WeeklyHoursEditorProps {
  initialHours: CourtWorkingHour[]
  onSave: (hours: CourtWorkingHour[]) => Promise<void>
  saving: boolean
}

export function WeeklyHoursEditor({ initialHours, onSave, saving }: WeeklyHoursEditorProps) {
  const [hours, setHours] = useState<CourtWorkingHour[]>(() =>
    Array.from({ length: 7 }, (_, day) => {
      const existing = initialHours.find((h) => h.day_of_week === day)
      return existing ?? { day_of_week: day, is_closed: false, open_time: '08:00', close_time: '23:00' }
    }),
  )

  const updateDay = (day: number, patch: Partial<CourtWorkingHour>) => {
    setHours((prev) => prev.map((h) => (h.day_of_week === day ? { ...h, ...patch } : h)))
  }

  return (
    <div className="space-y-3">
      {hours.map((hour) => (
        <div key={hour.day_of_week} className="flex items-center gap-3">
          <span className="w-24 shrink-0 text-sm">{DAY_LABELS[hour.day_of_week]}</span>
          <Switch
            checked={!hour.is_closed}
            onCheckedChange={(checked) => updateDay(hour.day_of_week, { is_closed: !checked })}
          />
          {hour.is_closed ? (
            <span className="text-sm text-muted-foreground">Closed</span>
          ) : (
            <>
              <Input
                type="time"
                value={hour.open_time ?? ''}
                onChange={(e) => updateDay(hour.day_of_week, { open_time: e.target.value })}
                className="w-32"
              />
              <span className="text-muted-foreground">to</span>
              <Input
                type="time"
                value={hour.close_time ?? ''}
                onChange={(e) => updateDay(hour.day_of_week, { close_time: e.target.value })}
                className="w-32"
              />
            </>
          )}
        </div>
      ))}
      <Button onClick={() => onSave(hours)} disabled={saving} className="mt-2">
        {saving ? 'Saving…' : 'Save hours'}
      </Button>
    </div>
  )
}
