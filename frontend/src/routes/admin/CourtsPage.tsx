import { useState } from 'react'
import { toast } from 'sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
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
import { WeeklyHoursEditor } from '@/components/admin/WeeklyHoursEditor'
import {
  useAdminCourts,
  useCreateCourt,
  useUpdateCourt,
  useUpdateWorkingHours,
  useDeleteCourt,
} from '@/api/admin/courts'
import { extractErrorMessage } from '@/lib/api-client'
import type { Court } from '@/types'
import { Plus } from 'lucide-react'

export default function CourtsPage() {
  const { data: courts, isLoading } = useAdminCourts()

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Courts</h1>
          <p className="text-sm text-muted-foreground">Manage courts and their weekly working hours.</p>
        </div>
        <AddCourtDialog />
      </div>

      {isLoading && <p className="text-muted-foreground">Loading…</p>}

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {courts?.map((court) => <CourtCard key={court.id} court={court} />)}
      </div>
    </div>
  )
}

function AddCourtDialog() {
  const [open, setOpen] = useState(false)
  const [name, setName] = useState('')
  const [description, setDescription] = useState('')
  const createCourt = useCreateCourt()

  const submit = async () => {
    try {
      await createCourt.mutateAsync({ name, description: description || undefined })
      toast.success('Court added.')
      setOpen(false)
      setName('')
      setDescription('')
    } catch (e) {
      toast.error(extractErrorMessage(e))
    }
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button><Plus className="mr-1 h-4 w-4" /> Add court</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader><DialogTitle>Add a court</DialogTitle></DialogHeader>
        <div className="space-y-3">
          <div className="space-y-1.5">
            <Label htmlFor="court-name">Name</Label>
            <Input id="court-name" value={name} onChange={(e) => setName(e.target.value)} />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="court-desc">Description (optional)</Label>
            <Input id="court-desc" value={description} onChange={(e) => setDescription(e.target.value)} />
          </div>
          <Button onClick={submit} disabled={!name || createCourt.isPending} className="w-full">
            {createCourt.isPending ? 'Adding…' : 'Add court'}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  )
}

function CourtCard({ court }: { court: Court }) {
  const updateCourt = useUpdateCourt()
  const updateHours = useUpdateWorkingHours()
  const deleteCourt = useDeleteCourt()

  return (
    <div className="rounded-lg border bg-card p-4">
      <div className="flex items-start justify-between">
        <div>
          <p className="font-medium">{court.name}</p>
          {court.description && <p className="text-sm text-muted-foreground">{court.description}</p>}
        </div>
        <Switch
          checked={court.is_active}
          onCheckedChange={async (checked) => {
            try {
              await updateCourt.mutateAsync({ id: court.id, is_active: checked })
            } catch (e) {
              toast.error(extractErrorMessage(e))
            }
          }}
        />
      </div>

      <div className="mt-4 flex gap-2">
        <Dialog>
          <DialogTrigger asChild>
            <Button size="sm" variant="outline">Edit hours</Button>
          </DialogTrigger>
          <DialogContent className="max-h-[85vh] overflow-y-auto">
            <DialogHeader><DialogTitle>{court.name} — Working hours</DialogTitle></DialogHeader>
            <WeeklyHoursEditor
              initialHours={court.working_hours}
              saving={updateHours.isPending}
              onSave={async (hours) => {
                try {
                  await updateHours.mutateAsync({ courtId: court.id, hours })
                  toast.success('Working hours updated.')
                } catch (e) {
                  toast.error(extractErrorMessage(e))
                }
              }}
            />
          </DialogContent>
        </Dialog>

        <AlertDialog>
          <AlertDialogTrigger asChild>
            <Button size="sm" variant="destructive">Delete</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete {court.name}?</AlertDialogTitle>
              <AlertDialogDescription>
                Courts with upcoming bookings can't be deleted — deactivate instead using the toggle above.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction
                onClick={async () => {
                  try {
                    await deleteCourt.mutateAsync(court.id)
                    toast.success('Court deleted.')
                  } catch (e) {
                    toast.error(extractErrorMessage(e, 'This court has upcoming bookings and cannot be deleted.'))
                  }
                }}
              >
                Delete
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </div>
  )
}
