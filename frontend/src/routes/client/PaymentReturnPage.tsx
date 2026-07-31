import { useEffect, useState } from 'react'
import { useNavigate, useSearchParams, Link } from 'react-router-dom'
import { usePaymentStatus } from '@/api/client/bookings'
import { Loader2 } from 'lucide-react'

const MAX_WAIT_MS = 45_000

export default function PaymentReturnPage() {
  const [searchParams] = useSearchParams()
  const reference = searchParams.get('reference') ?? undefined
  const navigate = useNavigate()
  const [timedOut, setTimedOut] = useState(false)

  const { data } = usePaymentStatus(reference, true)

  useEffect(() => {
    if (data?.payment_status === 'paid' || data?.status === 'cancelled' || data?.status === 'expired') {
      const timer = setTimeout(() => navigate(`/bookings/${reference}`, { replace: true }), 600)
      return () => clearTimeout(timer)
    }
  }, [data, navigate, reference])

  useEffect(() => {
    const timer = setTimeout(() => setTimedOut(true), MAX_WAIT_MS)
    return () => clearTimeout(timer)
  }, [])

  if (!reference) {
    return (
      <div className="mx-auto max-w-md px-4 py-24 text-center">
        <p>Missing booking reference.</p>
        <Link to="/" className="mt-4 inline-block text-primary hover:underline">
          Return home
        </Link>
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-md px-4 py-24 text-center">
      <Loader2 className="mx-auto h-10 w-10 animate-spin text-primary" />
      <h1 className="mt-4 text-xl font-semibold">Confirming your payment…</h1>
      <p className="mt-2 text-sm text-muted-foreground">This usually only takes a few seconds.</p>

      {timedOut && (
        <div className="mt-6 rounded-lg border bg-card p-4 text-sm">
          <p>Still waiting on confirmation. If you cancelled or the payment didn't go through, your held slot will be released shortly.</p>
          <div className="mt-3 flex justify-center gap-4">
            <Link to={`/bookings/${reference}`} className="text-primary hover:underline">
              Check booking status
            </Link>
            <Link to="/" className="text-primary hover:underline">
              Start over
            </Link>
          </div>
        </div>
      )}
    </div>
  )
}
