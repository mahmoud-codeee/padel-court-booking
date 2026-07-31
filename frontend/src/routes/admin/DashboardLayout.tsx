import { NavLink, Outlet } from 'react-router-dom'
import { useAdminAuthStore } from '@/stores/adminAuthStore'
import { useAdminLogout } from '@/api/admin/auth'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { CalendarClock, LayoutGrid, CircleDollarSign, CalendarX2, LogOut } from 'lucide-react'

const navItems = [
  { to: '/admin/bookings', label: 'Bookings', icon: CalendarClock },
  { to: '/admin/courts', label: 'Courts', icon: LayoutGrid },
  { to: '/admin/closures', label: 'Closures', icon: CalendarX2 },
  { to: '/admin/pricing', label: 'Pricing', icon: CircleDollarSign },
]

export default function DashboardLayout() {
  const admin = useAdminAuthStore((s) => s.admin)
  const logout = useAdminLogout()

  return (
    <div className="min-h-screen bg-background md:flex">
      <aside className="border-b bg-card md:w-56 md:shrink-0 md:border-b-0 md:border-r">
        <div className="px-4 py-5">
          <p className="font-semibold">Padel Admin</p>
          {admin && <p className="truncate text-xs text-muted-foreground">{admin.email}</p>}
        </div>
        <nav className="flex gap-1 overflow-x-auto px-2 pb-2 md:flex-col md:overflow-visible md:pb-4">
          {navItems.map(({ to, label, icon: Icon }) => (
            <NavLink
              key={to}
              to={to}
              className={({ isActive }) =>
                cn(
                  'flex shrink-0 items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                  isActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-muted',
                )
              }
            >
              <Icon className="h-4 w-4" />
              {label}
            </NavLink>
          ))}
        </nav>
        <div className="hidden px-2 pb-4 md:block">
          <Button variant="ghost" size="sm" className="w-full justify-start gap-2" onClick={() => logout.mutate()}>
            <LogOut className="h-4 w-4" />
            Log out
          </Button>
        </div>
      </aside>

      <main className="flex-1 p-4 md:p-8">
        <Outlet />
      </main>
    </div>
  )
}
