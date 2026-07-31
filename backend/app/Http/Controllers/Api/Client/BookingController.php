<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreBookingRequest;
use App\Http\Resources\Client\BookingResource;
use App\Services\Booking\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookings->createBooking(
            $request->validated('slots'),
            $request->validated('customer'),
            PaymentMethod::from($request->validated('payment_method')),
        );

        return (new BookingResource($booking))->response()->setStatusCode(201);
    }

    public function show(string $reference): BookingResource
    {
        $booking = $this->bookings->findByReference($reference);
        abort_if($booking === null, 404, 'Booking not found.');

        return new BookingResource($booking);
    }

    public function paymentStatus(string $reference): JsonResponse
    {
        $booking = $this->bookings->findByReference($reference);
        abort_if($booking === null, 404, 'Booking not found.');

        return response()->json(['data' => [
            'status' => $booking->status->value,
            'payment_status' => $booking->payment_status->value,
        ]]);
    }
}
