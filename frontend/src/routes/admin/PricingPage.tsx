import { useEffect, useState } from 'react'
import { toast } from 'sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import {
  useAdminPricingSettings,
  useUpdatePricingSettings,
  useAdminDiscountTiers,
  useCreateDiscountTier,
  useUpdateDiscountTier,
  useDeleteDiscountTier,
} from '@/api/admin/pricing'
import { extractErrorMessage } from '@/lib/api-client'
import type { DiscountTier } from '@/types'
import { Trash2, Plus } from 'lucide-react'

export default function PricingPage() {
  const { data: settings } = useAdminPricingSettings()
  const updateSettings = useUpdatePricingSettings()
  const [basePrice, setBasePrice] = useState('')

  useEffect(() => {
    if (settings) setBasePrice(settings.base_price_per_hour.toString())
  }, [settings])

  const { data: tiers } = useAdminDiscountTiers()

  return (
    <div className="max-w-2xl space-y-8">
      <div>
        <h1 className="text-2xl font-semibold">Pricing</h1>
        <p className="text-sm text-muted-foreground">Base hourly price and hour-based discount tiers.</p>
      </div>

      <div className="space-y-3 rounded-lg border bg-card p-4">
        <Label htmlFor="base-price">Base price per hour ({settings?.currency ?? '...'})</Label>
        <div className="flex gap-2">
          <Input
            id="base-price"
            type="number"
            step="0.01"
            min="0"
            value={basePrice}
            onChange={(e) => setBasePrice(e.target.value)}
            className="w-40"
          />
          <Button
            disabled={updateSettings.isPending || !basePrice}
            onClick={async () => {
              try {
                await updateSettings.mutateAsync({ base_price_per_hour: Number(basePrice) })
                toast.success('Base price updated.')
              } catch (e) {
                toast.error(extractErrorMessage(e))
              }
            }}
          >
            Save
          </Button>
        </div>
      </div>

      <div className="space-y-3">
        <div className="flex items-center justify-between">
          <h2 className="font-medium">Discount tiers</h2>
          <AddTierButton />
        </div>
        <div className="overflow-x-auto rounded-lg border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Hours</TableHead>
                <TableHead>Price / hour</TableHead>
                <TableHead>Active</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {tiers?.length === 0 && (
                <TableRow><TableCell colSpan={4} className="text-center text-muted-foreground">No tiers — base price applies to every booking.</TableCell></TableRow>
              )}
              {tiers?.map((tier) => <TierRow key={tier.id} tier={tier} />)}
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  )
}

function TierRow({ tier }: { tier: DiscountTier }) {
  const updateTier = useUpdateDiscountTier()
  const deleteTier = useDeleteDiscountTier()

  return (
    <TableRow>
      <TableCell>
        {tier.min_hours}{tier.max_hours ? `–${tier.max_hours}` : '+'} hr{tier.max_hours !== 1 ? 's' : ''}
      </TableCell>
      <TableCell>{tier.price_per_hour.toFixed(2)}</TableCell>
      <TableCell>
        <Switch
          checked={tier.is_active}
          onCheckedChange={async (checked) => {
            try {
              await updateTier.mutateAsync({
                id: tier.id,
                min_hours: tier.min_hours,
                max_hours: tier.max_hours,
                price_per_hour: tier.price_per_hour,
                is_active: checked,
              })
            } catch (e) {
              toast.error(extractErrorMessage(e))
            }
          }}
        />
      </TableCell>
      <TableCell className="text-right">
        <Button
          size="icon"
          variant="ghost"
          onClick={async () => {
            try {
              await deleteTier.mutateAsync(tier.id)
              toast.success('Tier removed.')
            } catch (e) {
              toast.error(extractErrorMessage(e))
            }
          }}
        >
          <Trash2 className="h-4 w-4" />
        </Button>
      </TableCell>
    </TableRow>
  )
}

function AddTierButton() {
  const [open, setOpen] = useState(false)
  const [minHours, setMinHours] = useState('')
  const [maxHours, setMaxHours] = useState('')
  const [price, setPrice] = useState('')
  const createTier = useCreateDiscountTier()

  if (!open) {
    return (
      <Button size="sm" variant="outline" onClick={() => setOpen(true)}>
        <Plus className="mr-1 h-4 w-4" /> Add tier
      </Button>
    )
  }

  const submit = async () => {
    try {
      await createTier.mutateAsync({
        min_hours: Number(minHours),
        max_hours: maxHours ? Number(maxHours) : null,
        price_per_hour: Number(price),
      })
      toast.success('Tier added.')
      setOpen(false)
      setMinHours('')
      setMaxHours('')
      setPrice('')
    } catch (e) {
      toast.error(extractErrorMessage(e, 'This hour range overlaps an existing tier.'))
    }
  }

  return (
    <div className="flex items-end gap-2 rounded-lg border bg-card p-3">
      <div className="space-y-1">
        <Label className="text-xs">Min hours</Label>
        <Input type="number" min="1" value={minHours} onChange={(e) => setMinHours(e.target.value)} className="w-20" />
      </div>
      <div className="space-y-1">
        <Label className="text-xs">Max hours</Label>
        <Input type="number" min="1" placeholder="∞" value={maxHours} onChange={(e) => setMaxHours(e.target.value)} className="w-20" />
      </div>
      <div className="space-y-1">
        <Label className="text-xs">Price/hr</Label>
        <Input type="number" step="0.01" min="0" value={price} onChange={(e) => setPrice(e.target.value)} className="w-24" />
      </div>
      <Button size="sm" onClick={submit} disabled={!minHours || !price || createTier.isPending}>Save</Button>
      <Button size="sm" variant="ghost" onClick={() => setOpen(false)}>Cancel</Button>
    </div>
  )
}
