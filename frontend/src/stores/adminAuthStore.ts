import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import type { AdminInfo } from '@/types'

interface AdminAuthState {
  token: string | null
  admin: AdminInfo | null
  setAuth: (token: string, admin: AdminInfo) => void
  logout: () => void
}

export const useAdminAuthStore = create<AdminAuthState>()(
  persist(
    (set) => ({
      token: null,
      admin: null,
      setAuth: (token, admin) => set({ token, admin }),
      logout: () => set({ token: null, admin: null }),
    }),
    { name: 'padel-admin-auth' },
  ),
)
