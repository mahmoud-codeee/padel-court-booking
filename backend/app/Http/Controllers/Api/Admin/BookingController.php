<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexBookingsRequest;
use App\Http\Resources\Admin\AdminBookingResource;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function index(IndexBookingsRequest $request): AnonymousResourceCollection
    {
        $query = Booking::query()->with(['slots.court'])->latest();

        if ($courtId = $request->validated('court_id')) {
            $query->whereHas('slots', fn ($q) => $q->where('court_id', $courtId));
        }
        if ($date = $request->validated('date')) {
            $query->whereHas('slots', fn ($q) => $q->where('slot_date', $date));
        }
        if ($status = $request->validated('status')) {
            $query->where('status', $status);
        }
        if ($paymentMethod = $request->validated('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }
        if ($phone = $request->validated('phone')) {
            $query->where('customer_phone', 'like', '%'.$phone.'%');
        }

        $bookings = $query->paginate($request->validated('per_page') ?? 20);

        return AdminBookingResource::collection($bookings);
    }

    public function markPaid(Booking $booking): AdminBookingResource
    {
        $booking->update(['payment_status' => PaymentStatus::Paid]);

        return new AdminBookingResource($booking->fresh(['slots.court']));
    }

    public function cancel(Request $request, Booking $booking): AdminBookingResource
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:255']]);

        $this->bookings->releaseSlots($booking, BookingStatus::Cancelled);

        if ($request->filled('admin_notes')) {
            $booking->update(['admin_notes' => $request->input('admin_notes')]);
        }

        return new AdminBookingResource($booking->fresh(['slots.court']));
    }
}
