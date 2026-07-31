export type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'expired'
export type PaymentMethod = 'cash' | 'online'
export type PaymentStatus = 'unpaid' | 'awaiting_payment' | 'paid' | 'failed' | 'refund_pending' | 'refunded'

export interface AvailabilitySlot {
  hour: number
  available: boolean
}

export interface DiscountTier {
  id: number
  min_hours: number
  max_hours: number | null
  price_per_hour: number
  is_active: boolean
}

export interface PricingInfo {
  base_price_per_hour: number
  currency: string
  discount_tiers: DiscountTier[]
}

export interface CustomerInfo {
  phone: string
  name?: string | null
  email?: string | null
}

export interface RequestedSlot {
  date: string
  hour: number
}

export interface ClientBookingSlot {
  date: string
  hour: number
}

export interface ClientBooking {
  reference: string
  status: BookingStatus
  payment_method: PaymentMethod
  payment_status: PaymentStatus
  total_hours: number
  price_per_hour: number
  total_amount: number
  currency: string
  customer_phone: string
  customer_name: string | null
  customer_email: string | null
  slots: ClientBookingSlot[]
  payment_checkout_url: string | null
  created_at: string
}

export interface CourtWorkingHour {
  day_of_week: number
  is_closed: boolean
  open_time: string | null
  close_time: string | null
}

export interface Court {
  id: number
  name: string
  description: string | null
  is_active: boolean
  working_hours: CourtWorkingHour[]
  created_at: string
}

export interface CourtClosure {
  id: number
  batch_id: string
  court_id: number | null
  court_name: string
  closure_date: string
  start_time: string | null
  end_time: string | null
  is_full_day: boolean
  reason: string | null
}

export interface PricingSetting {
  base_price_per_hour: number
  currency: string
}

export interface AdminBookingSlot {
  date: string
  hour: number
  court_id: number
  court_name: string
}

export interface AdminBooking {
  id: number
  reference: string
  status: BookingStatus
  payment_method: PaymentMethod
  payment_status: PaymentStatus
  customer_phone: string
  customer_name: string | null
  customer_email: string | null
  total_hours: number
  price_per_hour: number
  total_amount: number
  currency: string
  admin_notes: string | null
  slots: AdminBookingSlot[]
  hold_expires_at: string | null
  confirmed_at: string | null
  cancelled_at: string | null
  created_at: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface AdminInfo {
  id: number
  name: string
  email: string
}
