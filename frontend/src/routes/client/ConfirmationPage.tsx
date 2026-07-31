import { Link, useParams } from 'react-router-dom'
import { useBooking } from '@/api/client/bookings'
import { Skeleton } from '@/components/ui/skeleton'
import { Badge } from '@/components/ui/badge'
import { formatCurrency, formatDateLabel, formatHourLabel } from '@/lib/utils'
import { CheckCircle2, Clock, XCircle } from 'lucide-react'

const statusMeta: Record<string, { label: string; icon: typeof CheckCircle2; className: string }> = {
  confirmed: { label: 'Confirmed', icon: CheckCircle2, className: 'text-success' },
  pending: { label: 'Pending payment', icon: Clock, className: 'text-warning' },
  cancelled: { label: 'Cancelled', icon: XCircle, className: 'text-destructive' },
  expired: { label: 'Expired', icon: XCircle, className: 'text-destructive' },
}

export default function ConfirmationPage() {
  const { reference } = useParams<{ reference: string }>()
  const { data: booking, isLoading, isError } = useBooking(reference)

  if (isLoading) {
    return (
      <div className="mx-auto max-w-xl space-y-4 px-4 py-16">
        <Skeleton className="h-8 w-1/2" />
        <Skeleton className="h-32 w-full" />
      </div>
    )
  }

  if (isError || !booking) {
    return (
      <div className="mx-auto max-w-xl px-4 py-16 text-center">
        <p className="text-lg font-medium">Booking not found</p>
        <Link to="/" className="mt-4 inline-block text-primary hover:underline">
          Book a court
        </Link>
      </div>
    )
  }

  const meta = statusMeta[booking.status] ?? statusMeta.pending
  const Icon = meta.icon

  return (
    <div className="mx-auto max-w-xl px-4 py-12">
      <div className="text-center">
        <Icon className={`mx-auto h-14 w-14 ${meta.className}`} />
        <h1 className="mt-4 text-2xl font-semibold">{meta.label}</h1>
        <p className="mt-1 text-sm text-muted-foreground">Reference: {booking.reference}</p>
      </div>

      <div className="mt-8 rounded-lg border bg-card p-5">
        <div className="flex items-center justify-between border-b pb-3">
          <span className="text-sm text-muted-foreground">Payment</span>
          <div className="flex gap-2">
            <Badge variant="secondary">{booking.payment_method === 'cash' ? 'Pay on arrival' : 'Paid online'}</Badge>
            <Badge variant={booking.payment_status === 'paid' ? 'default' : 'outline'}>
              {booking.payment_status.replace('_', ' ')}
            </Badge>
          </div>
        </div>

        <div className="mt-3 space-y-2">
          {booking.slots.map((slot) => (
            <div key={`${slot.date}-${slot.hour}`} className="flex justify-between text-sm">
              <span>{formatDateLabel(slot.date)}</span>
              <span className="text-muted-foreground">{formatHourLabel(slot.hour)}</span>
            </div>
          ))}
        </div>

        <div className="mt-3 flex justify-between border-t pt-3 font-medium">
          <span>Total</span>
          <span>{formatCurrency(booking.total_amount, booking.currency)}</span>
        </div>
      </div>

      <p className="mt-6 text-center text-sm text-muted-foreground">
        A confirmation was sent to {booking.customer_phone}. Save this page's link for your records.
      </p>

      <div className="mt-6 text-center">
        <Link to="/" className="text-primary hover:underline">
          Book another court
        </Link>
      </div>
    </div>
  )
}
