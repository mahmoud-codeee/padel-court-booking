import { useState } from 'react'
import { toast } from 'sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { useAdminCourts } from '@/api/admin/courts'
import { useAdminClosures, useCreateClosure, useDeleteClosure } from '@/api/admin/closures'
import { extractErrorMessage } from '@/lib/api-client'
import { formatDateLabel } from '@/lib/utils'
import { Trash2 } from 'lucide-react'

export default function ClosuresPage() {
  const { data: courts } = useAdminCourts()
  const { data: closures, isLoading } = useAdminClosures()
  const createClosure = useCreateClosure()
  const deleteClosure = useDeleteClosure()

  const [allCourts, setAllCourts] = useState(true)
  const [selectedCourtIds, setSelectedCourtIds] = useState<number[]>([])
  const [date, setDate] = useState('')
  const [fullDay, setFullDay] = useState(true)
  const [startTime, setStartTime] = useState('')
  const [endTime, setEndTime] = useState('')
  const [reason, setReason] = useState('')

  const toggleCourt = (id: number) => {
    setSelectedCourtIds((prev) => (prev.includes(id) ? prev.filter((c) => c !== id) : [...prev, id]))
  }

  const submit = async () => {
    if (!date) {
      toast.error('Pick a date.')
      return
    }
    if (!allCourts && selectedCourtIds.length === 0) {
      toast.error('Select at least one court, or mark this as an all-courts closure.')
      return
    }
    try {
      await createClosure.mutateAsync({
        all_courts: allCourts,
        court_ids: allCourts ? undefined : selectedCourtIds,
        closure_date: date,
        start_time: fullDay ? undefined : startTime || undefined,
        end_time: fullDay ? undefined : endTime || undefined,
        reason: reason || undefined,
      })
      toast.success('Closure created.')
      setDate('')
      setReason('')
      setSelectedCourtIds([])
    } catch (e) {
      toast.error(extractErrorMessage(e))
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">Closures</h1>
        <p className="text-sm text-muted-foreground">Close a court, several courts, or the whole venue for a date.</p>
      </div>

      <div className="max-w-xl space-y-4 rounded-lg border bg-card p-4">
        <div className="flex items-center gap-2">
          <Switch checked={allCourts} onCheckedChange={setAllCourts} />
          <span className="text-sm">Close all courts</span>
        </div>

        {!allCourts && (
          <div className="flex flex-wrap gap-2">
            {courts?.map((c) => (
              <button
                key={c.id}
                type="button"
                onClick={() => toggleCourt(c.id)}
                className={`rounded-full border px-3 py-1 text-sm ${selectedCourtIds.includes(c.id) ? 'border-primary bg-accent' : ''}`}
              >
                {c.name}
              </button>
            ))}
          </div>
        )}

        <div className="space-y-1.5">
          <Label htmlFor="closure-date">Date</Label>
          <Input id="closure-date" type="date" value={date} onChange={(e) => setDate(e.target.value)} />
        </div>

        <div className="flex items-center gap-2">
          <Switch checked={fullDay} onCheckedChange={setFullDay} />
          <span className="text-sm">Full day</span>
        </div>

        {!fullDay && (
          <div className="flex gap-3">
            <div className="space-y-1.5">
              <Label htmlFor="start-time">From</Label>
              <Input id="start-time" type="time" value={startTime} onChange={(e) => setStartTime(e.target.value)} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="end-time">To</Label>
              <Input id="end-time" type="time" value={endTime} onChange={(e) => setEndTime(e.target.value)} />
            </div>
          </div>
        )}

        <div className="space-y-1.5">
          <Label htmlFor="reason">Reason (optional)</Label>
          <Input id="reason" value={reason} onChange={(e) => setReason(e.target.value)} placeholder="Maintenance, holiday…" />
        </div>

        <Button onClick={submit} disabled={createClosure.isPending}>
          {createClosure.isPending ? 'Creating…' : 'Create closure'}
        </Button>
      </div>

      <div className="overflow-x-auto rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Date</TableHead>
              <TableHead>Court</TableHead>
              <TableHead>Time</TableHead>
              <TableHead>Reason</TableHead>
              <TableHead className="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && <TableRow><TableCell colSpan={5} className="text-center text-muted-foreground">Loading…</TableCell></TableRow>}
            {!isLoading && closures?.length === 0 && (
              <TableRow><TableCell colSpan={5} className="text-center text-muted-foreground">No upcoming closures.</TableCell></TableRow>
            )}
            {closures?.map((closure) => (
              <TableRow key={closure.id}>
                <TableCell>{formatDateLabel(closure.closure_date)}</TableCell>
                <TableCell>{closure.court_name}</TableCell>
                <TableCell>{closure.is_full_day ? 'Full day' : `${closure.start_time} - ${closure.end_time}`}</TableCell>
                <TableCell className="text-muted-foreground">{closure.reason ?? '—'}</TableCell>
                <TableCell className="text-right">
                  <Button
                    size="icon"
                    variant="ghost"
                    onClick={async () => {
                      try {
                        await deleteClosure.mutateAsync(closure.id)
                        toast.success('Closure removed.')
                      } catch (e) {
                        toast.error(extractErrorMessage(e))
                      }
                    }}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  )
}
