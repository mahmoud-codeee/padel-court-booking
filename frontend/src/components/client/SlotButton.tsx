import { memo } from 'react'
import { Button } from '@/components/ui/button'
import { cn, formatHourLabel } from '@/lib/utils'

interface SlotButtonProps {
  hour: number
  available: boolean
  selected: boolean
  onToggle: (hour: number) => void
}

function SlotButtonImpl({ hour, available, selected, onToggle }: SlotButtonProps) {
  return (
    <Button
      type="button"
      variant={selected ? 'default' : available ? 'outline' : 'ghost'}
      disabled={!available}
      onClick={() => onToggle(hour)}
      className={cn(
        'h-11 w-full font-normal tabular-nums',
        !available && 'opacity-40 line-through',
        selected && 'ring-2 ring-primary ring-offset-2 ring-offset-background',
      )}
    >
      {formatHourLabel(hour)}
    </Button>
  )
}

export const SlotButton = memo(SlotButtonImpl)
