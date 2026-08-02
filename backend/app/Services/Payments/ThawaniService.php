<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class ThawaniService
{
    private string $baseUrl;

    private ?string $secretKey;

    private ?string $publishableKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.thawani.base_url'), '/');
        $this->secretKey = config('services.thawani.secret_key');
        $this->publishableKey = config('services.thawani.publishable_key');
    }

    /**
     * Creates a Thawani checkout session for a booking and persists a Payment
     * row tracking it. Amount is converted to baisa (Thawani's smallest unit —
     * 1 OMR = 1000 baisa).
     */
    public function createCheckoutSession(Booking $booking): Payment
    {
        $clientReferenceId = (string) Str::uuid();
        $unitAmountBaisa = (int) round(((float) $booking->total_amount) * 1000);

        $response = Http::withHeaders(['thawani-api-key' => $this->secretKey])
            ->post("{$this->baseUrl}/checkout/session", [
                'client_reference_id' => $clientReferenceId,
                'mode' => 'payment',
                'products' => [[
                    'name' => "Padel court booking ({$booking->total_hours}h)",
                    'quantity' => 1,
                    'unit_amount' => $unitAmountBaisa,
                ]],
                'success_url' => config('services.thawani.success_url')."?reference={$booking->reference}",
                'cancel_url' => config('services.thawani.cancel_url')."?reference={$booking->reference}",
                'metadata' => ['booking_reference' => $booking->reference],
            ]);

        if (! $response->successful() || ! ($response->json('data.session_id'))) {
            throw new RuntimeException('Thawani checkout session creation failed: '.$response->body());
        }

        $sessionId = $response->json('data.session_id');
        $checkoutUrl = "https://uatcheckout.thawani.om/pay/{$sessionId}?key={$this->publishableKey}";

        return Payment::query()->create([
            'booking_id' => $booking->id,
            'provider' => 'thawani',
            'client_reference_id' => $clientReferenceId,
            'thawani_session_id' => $sessionId,
            'thawani_checkout_url' => $checkoutUrl,
            'thawani_status' => $response->json('data.payment_status', 'unpaid'),
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'raw_session_response' => $response->json(),
        ]);
    }

    /**
     * Re-fetches a session's status directly from Thawani — the webhook body
     * itself is never trusted as the source of truth, only used as a trigger.
     */
    public function fetchSessionStatus(string $sessionId): array
    {
        $response = Http::withHeaders(['thawani-api-key' => $this->secretKey])
            ->get("{$this->baseUrl}/checkout/session/{$sessionId}");

        if (! $response->successful()) {
            throw new RuntimeException("Failed to fetch Thawani session {$sessionId}: ".$response->body());
        }

        return [
            'payment_status' => $response->json('data.payment_status'),
            'raw' => $response->json(),
        ];
    }
}
