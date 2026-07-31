import { useEffect, useRef } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useForm, Controller } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { toast } from 'sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { PriceBreakdown, ClearCartButton } from '@/components/client/PriceBreakdown'
import { useBookingCartStore } from '@/stores/bookingCartStore'
import { useCreateBooking } from '@/api/client/bookings'
import { extractErrorMessage } from '@/lib/api-client'
import type { PaymentMethod } from '@/types'
import axios from 'axios'

const schema = z.object({
  phone: z.string().min(6, 'Enter a valid phone number').max(20),
  name: z.string().max(100).optional().or(z.literal('')),
  email: z.string().email('Enter a valid email').max(150).optional().or(z.literal('')),
  paymentMethod: z.enum(['cash', 'online']),
})

type FormValues = z.infer<typeof schema>

export default function CheckoutPage() {
  const navigate = useNavigate()
  const items = useBookingCartStore((s) => s.items)
  const customer = useBookingCartStore((s) => s.customer)
  const paymentMethod = useBookingCartStore((s) => s.paymentMethod)
  const setCustomer = useBookingCartStore((s) => s.setCustomer)
  const setPaymentMethod = useBookingCartStore((s) => s.setPaymentMethod)
  const removeItems = useBookingCartStore((s) => s.removeItems)
  const clear = useBookingCartStore((s) => s.clear)
  const createBooking = useCreateBooking()
  // Guards the empty-cart redirect below: clearing the cart on a successful
  // submission would otherwise re-trigger that effect while this page is
  // still mounted (lazy route transitions don't unmount synchronously),
  // which can win the race and bounce the user back to "/" instead of
  // landing on the confirmation page.
  const submittedRef = useRef(false)

  const {
    register,
    handleSubmit,
    control,
    formState: { errors },
  } = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: {
      phone: customer.phone,
      name: customer.name,
      email: customer.email,
      paymentMethod,
    },
  })

  useEffect(() => {
    if (items.length === 0 && !submittedRef.current) navigate('/', { replace: true })
  }, [items.length, navigate])

  const onSubmit = handleSubmit(async (values) => {
    setCustomer({ phone: values.phone, name: values.name ?? '', email: values.email ?? '' })
    setPaymentMethod(values.paymentMethod as PaymentMethod)

    try {
      const booking = await createBooking.mutateAsync({
        slots: items,
        customer: {
          phone: values.phone,
          name: values.name || undefined,
          email: values.email || undefined,
        },
        payment_method: values.paymentMethod as PaymentMethod,
      })

      submittedRef.current = true

      if (booking.payment_method === 'online' && booking.payment_checkout_url) {
        clear()
        window.location.href = booking.payment_checkout_url
        return
      }

      navigate(`/bookings/${booking.reference}`)
      clear()
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 409) {
        const conflicting = (error.response.data?.conflicting_slots ?? []) as { date: string; hour: number }[]
        const conflictKeys = new Set(conflicting.map((c) => `${c.date}|${c.hour}`))
        removeItems((item) => conflictKeys.has(`${item.date}|${item.hour}`))
        toast.error('Some selected slots were just booked by someone else and have been removed. Please review and try again.')
        return
      }
      toast.error(extractErrorMessage(error, 'Could not create your booking. Please try again.'))
    }
  })

  if (items.length === 0) return null

  return (
    <div className="min-h-screen pb-16">
      <header className="border-b bg-card">
        <div className="mx-auto max-w-2xl px-4 py-6">
          <Link to="/" className="text-sm text-muted-foreground hover:underline">
            &larr; Back to slot selection
          </Link>
          <h1 className="mt-2 text-2xl font-semibold">Review &amp; Book</h1>
        </div>
      </header>

      <main className="mx-auto max-w-2xl space-y-6 px-4 py-6">
        <div className="flex items-center justify-between">
          <h2 className="font-medium">Your selection</h2>
          <ClearCartButton onClear={clear} />
        </div>
        <PriceBreakdown items={items} onRemove={(item) => removeItems((i) => i.date === item.date && i.hour === item.hour)} />

        <form onSubmit={onSubmit} className="space-y-5">
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1.5 sm:col-span-2">
              <Label htmlFor="phone">Phone number *</Label>
              <Input id="phone" placeholder="+968 9xxx xxxx" {...register('phone')} />
              {errors.phone && <p className="text-sm text-destructive">{errors.phone.message}</p>}
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="name">Name (optional)</Label>
              <Input id="name" {...register('name')} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="email">Email (optional)</Label>
              <Input id="email" type="email" {...register('email')} />
              {errors.email && <p className="text-sm text-destructive">{errors.email.message}</p>}
            </div>
          </div>

          <div className="space-y-2">
            <Label>Payment method</Label>
            <Controller
              control={control}
              name="paymentMethod"
              render={({ field }) => (
                <div className="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    onClick={() => field.onChange('cash')}
                    className={`rounded-lg border p-4 text-left transition-colors ${field.value === 'cash' ? 'border-primary bg-accent' : 'hover:bg-muted'}`}
                  >
                    <p className="font-medium">Pay on arrival</p>
                    <p className="text-sm text-muted-foreground">Cash at the venue</p>
                  </button>
                  <button
                    type="button"
                    onClick={() => field.onChange('online')}
                    className={`rounded-lg border p-4 text-left transition-colors ${field.value === 'online' ? 'border-primary bg-accent' : 'hover:bg-muted'}`}
                  >
                    <p className="font-medium">Pay online</p>
                    <p className="text-sm text-muted-foreground">Via Thawani</p>
                  </button>
                </div>
              )}
            />
          </div>

          <Button type="submit" size="lg" className="w-full" disabled={createBooking.isPending}>
            {createBooking.isPending ? 'Booking…' : 'Confirm Booking'}
          </Button>
        </form>
      </main>
    </div>
  )
}
