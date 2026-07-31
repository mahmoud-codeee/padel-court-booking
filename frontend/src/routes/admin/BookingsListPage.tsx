import { useEffect, useMemo, useState } from 'react'
import { toast } from 'sonner'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { useAdminBookings, useCancelBooking, useMarkBookingPaid, type BookingFilters } from '@/api/admin/bookings'
import { useAdminCourts } from '@/api/admin/courts'
import { formatCurrency, formatDateLabel, formatHourLabel } from '@/lib/utils'
import { extractErrorMessage } from '@/lib/api-client'

const statusVariant: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
  confirmed: 'default',
  pending: 'secondary',
  cancelled: 'destructive',
  expired: 'outline',
}

export default function BookingsListPage() {
  const [filters, setFilters] = useState<BookingFilters>({ page: 1 })
  const [phoneInput, setPhoneInput] = useState('')
  const { data: courts } = useAdminCourts()
  const { data, isLoading } = useAdminBookings(filters)
  const markPaid = useMarkBookingPaid()
  const cancelBooking = useCancelBooking()

  useEffect(() => {
    const timer = setTimeout(() => {
      setFilters((f) => ({ ...f, phone: phoneInput || undefined, page: 1 }))
    }, 300)
    return () => clearTimeout(timer)
  }, [phoneInput])

  const update = (patch: Partial<BookingFilters>) => setFilters((f) => ({ ...f, ...patch, page: 1 }))

  const bookings = data?.data ?? []
  const meta = data?.meta

  const summary = useMemo(() => {
    if (!meta) return ''
    return `${meta.total} booking${meta.total !== 1 ? 's' : ''}`
  }, [meta])

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">Bookings</h1>
        <p className="text-sm text-muted-foreground">{summary}</p>
      </div>

      <div className="flex flex-wrap gap-3">
        <Input
          placeholder="Search phone…"
          value={phoneInput}
          onChange={(e) => setPhoneInput(e.target.value)}
          className="w-48"
        />
        <Select
          value={filters.court_id?.toString() ?? 'all'}
          onValueChange={(v) => update({ court_id: v === 'all' ? undefined : Number(v) })}
        >
          <SelectTrigger className="w-40"><SelectValue placeholder="Court" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All courts</SelectItem>
            {courts?.map((c) => (
              <SelectItem key={c.id} value={c.id.toString()}>{c.name}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        <Input
          type="date"
          className="w-40"
          value={filters.date ?? ''}
          onChange={(e) => update({ date: e.target.value || undefined })}
        />
        <Select
          value={filters.status ?? 'all'}
          onValueChange={(v) => update({ status: v === 'all' ? undefined : v })}
        >
          <SelectTrigger className="w-40"><SelectValue placeholder="Status" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All statuses</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="confirmed">Confirmed</SelectItem>
            <SelectItem value="cancelled">Cancelled</SelectItem>
            <SelectItem value="expired">Expired</SelectItem>
          </SelectContent>
        </Select>
        <Select
          value={filters.payment_method ?? 'all'}
          onValueChange={(v) => update({ payment_method: v === 'all' ? undefined : v })}
        >
          <SelectTrigger className="w-40"><SelectValue placeholder="Payment" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All methods</SelectItem>
            <SelectItem value="cash">Cash</SelectItem>
            <SelectItem value="online">Online</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div className="overflow-x-auto rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Phone</TableHead>
              <TableHead>Slots</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Payment</TableHead>
              <TableHead>Total</TableHead>
              <TableHead className="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading && (
              <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground">Loading…</TableCell></TableRow>
            )}
            {!isLoading && bookings.length === 0 && (
              <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground">No bookings found.</TableCell></TableRow>
            )}
            {bookings.map((booking) => (
              <TableRow key={booking.id}>
                <TableCell>
                  <div>{booking.customer_phone}</div>
                  {booking.customer_name && <div className="text-xs text-muted-foreground">{booking.customer_name}</div>}
                </TableCell>
                <TableCell className="max-w-56">
                  <div className="flex flex-wrap gap-x-2 text-xs text-muted-foreground">
                    {booking.slots.map((s) => (
                      <span key={`${s.date}-${s.hour}`}>
                        {formatDateLabel(s.date)} {formatHourLabel(s.hour)} ({s.court_name})
                      </span>
                    ))}
                  </div>
                </TableCell>
                <TableCell>
                  <Badge variant={statusVariant[booking.status] ?? 'outline'}>{booking.status}</Badge>
                </TableCell>
                <TableCell>
                  <div className="flex flex-col gap-1">
                    <span className="text-xs">{booking.payment_method}</span>
                    <Badge variant={booking.payment_status === 'paid' ? 'default' : 'outline'} className="w-fit">
                      {booking.payment_status.replace('_', ' ')}
                    </Badge>
                  </div>
                </TableCell>
                <TableCell>{formatCurrency(booking.total_amount, booking.currency)}</TableCell>
                <TableCell className="text-right">
                  <div className="flex justify-end gap-2">
                    {booking.payment_status !== 'paid' && booking.status !== 'cancelled' && (
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={async () => {
                          try {
                            await markPaid.mutateAsync(booking.id)
                            toast.success('Marked as paid.')
                          } catch (e) {
                            toast.error(extractErrorMessage(e))
                          }
                        }}
                      >
                        Mark paid
                      </Button>
                    )}
                    {booking.status !== 'cancelled' && (
                      <AlertDialog>
                        <AlertDialogTrigger asChild>
                          <Button size="sm" variant="destructive">Cancel</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Cancel this booking?</AlertDialogTitle>
                            <AlertDialogDescription>
                              This frees the slots immediately for other customers. This cannot be undone.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Back</AlertDialogCancel>
                            <AlertDialogAction
                              onClick={async () => {
                                try {
                                  await cancelBooking.mutateAsync({ id: booking.id })
                                  toast.success('Booking cancelled.')
                                } catch (e) {
                                  toast.error(extractErrorMessage(e))
                                }
                              }}
                            >
                              Cancel booking
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    )}
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-center gap-2">
          <Button
            variant="outline"
            size="sm"
            disabled={meta.current_page <= 1}
            onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) - 1 }))}
          >
            Previous
          </Button>
          <span className="text-sm text-muted-foreground">
            Page {meta.current_page} of {meta.last_page}
          </span>
          <Button
            variant="outline"
            size="sm"
            disabled={meta.current_page >= meta.last_page}
            onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) + 1 }))}
          >
            Next
          </Button>
        </div>
      )}
    </div>
  )
}
