import { Navigate, Route, Routes } from 'react-router-dom'
import { useAdminAuthStore } from '@/stores/adminAuthStore'
import LoginPage from './LoginPage'
import DashboardLayout from './DashboardLayout'
import BookingsListPage from './BookingsListPage'
import CourtsPage from './CourtsPage'
import ClosuresPage from './ClosuresPage'
import PricingPage from './PricingPage'

function AuthGuard({ children }: { children: React.ReactNode }) {
  const token = useAdminAuthStore((s) => s.token)
  if (!token) return <Navigate to="/admin/login" replace />
  return <>{children}</>
}

export default function AdminApp() {
  return (
    <Routes>
      <Route path="login" element={<LoginPage />} />
      <Route
        element={
          <AuthGuard>
            <DashboardLayout />
          </AuthGuard>
        }
      >
        <Route index element={<Navigate to="bookings" replace />} />
        <Route path="bookings" element={<BookingsListPage />} />
        <Route path="courts" element={<CourtsPage />} />
        <Route path="closures" element={<ClosuresPage />} />
        <Route path="pricing" element={<PricingPage />} />
      </Route>
    </Routes>
  )
}
