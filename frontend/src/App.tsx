import { lazy, Suspense } from 'react'
import { Routes, Route } from 'react-router-dom'

const BookingFlowPage = lazy(() => import('@/routes/client/BookingFlowPage'))
const CheckoutPage = lazy(() => import('@/routes/client/CheckoutPage'))
const ConfirmationPage = lazy(() => import('@/routes/client/ConfirmationPage'))
const PaymentReturnPage = lazy(() => import('@/routes/client/PaymentReturnPage'))
const AdminApp = lazy(() => import('@/routes/admin/AdminApp'))

function PageLoader() {
  return (
    <div className="flex min-h-screen items-center justify-center text-muted-foreground">
      Loading…
    </div>
  )
}

export default function App() {
  return (
    <Suspense fallback={<PageLoader />}>
      <Routes>
        <Route path="/" element={<BookingFlowPage />} />
        <Route path="/checkout" element={<CheckoutPage />} />
        <Route path="/bookings/return" element={<PaymentReturnPage />} />
        <Route path="/bookings/:reference" element={<ConfirmationPage />} />
        <Route path="/admin/*" element={<AdminApp />} />
      </Routes>
    </Suspense>
  )
}
