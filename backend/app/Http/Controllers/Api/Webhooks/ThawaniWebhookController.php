<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\ThawaniService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ThawaniWebhookController extends Controller
{
    public function __construct(private readonly ThawaniService $thawani) {}

    /**
     * The webhook body is never trusted as the source of truth — it's only a
     * trigger to re-fetch the session status directly from Thawani.
     */
    public function handle(Request $request): JsonResponse
    {
        $sessionId = $request->input('data.session_id')
            ?? $request->input('session_id')
            ?? $request->query('session_id');

        if (! $sessionId) {
            return response()->json(['message' => 'ignored: no session_id']);
        }

        $payment = Payment::query()->where('thawani_session_id', $sessionId)->first();

        if (! $payment) {
            Log::warning('Thawani webhook for unknown session.', ['session_id' => $sessionId]);

            return response()->json(['message' => 'ignored: unknown session']);
        }

        $fetched = $this->thawani->fetchSessionStatus($sessionId);
        $status = $fetched['payment_status'];

        DB::transaction(function () use ($payment, $status, $fetched) {
            $payment->refresh();
            $payment->update([
                'thawani_status' => $status,
                'raw_webhook_payload' => $fetched['raw'],
            ]);

            $booking = $payment->booking()->lockForUpdate()->first();

            if ($booking->payment_status === PaymentStatus::Paid) {
                return; // already processed, webhook retry — no-op
            }

            if ($status === 'paid') {
                if ($booking->status === BookingStatus::Expired || $booking->status === BookingStatus::Cancelled) {
                    // Hold already lapsed and the court may have been reassigned to
                    // someone else — do not silently re-confirm. Needs manual review.
                    Log::warning('Thawani payment confirmed after booking hold already lapsed.', [
                        'booking_reference' => $booking->reference,
                        'booking_status' => $booking->status->value,
                    ]);

                    return;
                }

                $booking->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => BookingStatus::Confirmed,
                    'confirmed_at' => $booking->confirmed_at ?? now(),
                    'hold_expires_at' => null,
                ]);
                $payment->update(['paid_at' => now()]);
            } elseif (in_array($status, ['failed', 'cancelled'], true)) {
                $booking->update(['payment_status' => PaymentStatus::Failed]);
            }
        });

        return response()->json(['message' => 'ok']);
    }
}
