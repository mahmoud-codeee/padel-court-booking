import { create } from 'zustand'
import { persist, createJSONStorage } from 'zustand/middleware'
import type { PaymentMethod, RequestedSlot } from '@/types'

interface CustomerForm {
  phone: string
  name: string
  email: string
}

interface BookingCartState {
  items: RequestedSlot[]
  customer: CustomerForm
  paymentMethod: PaymentMethod
  toggleItem: (item: RequestedSlot) => void
  hasItem: (item: RequestedSlot) => boolean
  removeItems: (predicate: (item: RequestedSlot) => boolean) => void
  clear: () => void
  setCustomer: (customer: Partial<CustomerForm>) => void
  setPaymentMethod: (method: PaymentMethod) => void
}

const keyOf = (item: RequestedSlot) => `${item.date}|${item.hour}`

export const useBookingCartStore = create<BookingCartState>()(
  persist(
    (set, get) => ({
      items: [],
      customer: { phone: '', name: '', email: '' },
      paymentMethod: 'cash',

      toggleItem: (item) => {
        const exists = get().items.some((i) => keyOf(i) === keyOf(item))
        set({
          items: exists
            ? get().items.filter((i) => keyOf(i) !== keyOf(item))
            : [...get().items, item],
        })
      },

      hasItem: (item) => get().items.some((i) => keyOf(i) === keyOf(item)),

      removeItems: (predicate) => set({ items: get().items.filter((i) => !predicate(i)) }),

      clear: () => set({ items: [] }),

      setCustomer: (customer) => set({ customer: { ...get().customer, ...customer } }),

      setPaymentMethod: (paymentMethod) => set({ paymentMethod }),
    }),
    {
      name: 'padel-booking-cart',
      storage: createJSONStorage(() => sessionStorage),
    },
  ),
)
