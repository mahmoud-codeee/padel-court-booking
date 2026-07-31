<?php

namespace App\Http\Controllers\Api\Client;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreBookingRequest;
use App\Http\Resources\Client\BookingResource;
use App\Services\Booking\BookingService;
use App\Services\Payments\ThawaniService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
        private readonly ThawaniService $thawani,
    ) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $paymentMethod = PaymentMethod::from($request->validated('payment_method'));

        $booking = $this->bookings->createBooking(
            $request->validated('slots'),
            $request->validated('customer'),
            $paymentMethod,
        );

        if ($paymentMethod === PaymentMethod::Online) {
            try {
                $this->thawani->createCheckoutSession($booking);
                $booking->load('payment');
            } catch (Throwable $e) {
                Log::error('Thawani checkout session creation failed, releasing held slots.', [
                    'booking_reference' => $booking->reference,
                    'error' => $e->getMessage(),
                ]);
                $this->bookings->releaseSlots($booking, BookingStatus::Cancelled);

                return response()->json([
                    'message' => 'Unable to start online payment right now. Please try again or pay on arrival.',
                ], 502);
            }
        }

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
