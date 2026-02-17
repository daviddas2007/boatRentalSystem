<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Cannot pay for a cancelled booking'], 422);
        }

        if ($booking->payment && $booking->payment->status === 'succeeded') {
            return response()->json(['message' => 'This booking has already been paid'], 422);
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($booking->total_price * 100),
                'currency' => 'usd',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'user_id' => $request->user()->id,
                ],
            ]);

            $payment = Payment::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => $request->user()->id,
                    'amount' => $booking->total_price,
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'status' => 'pending',
                ]
            );

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment initialization failed: ' . $e->getMessage()], 500);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->firstOrFail();

            if ($paymentIntent->status === 'succeeded') {
                $payment->update([
                    'status' => 'succeeded',
                    'stripe_charge_id' => $paymentIntent->latest_charge,
                ]);

                $payment->booking->update(['status' => 'confirmed']);

                return response()->json([
                    'message' => 'Payment confirmed successfully',
                    'payment' => $payment->load('booking'),
                ]);
            }

            return response()->json([
                'message' => 'Payment not yet confirmed',
                'status' => $paymentIntent->status,
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment confirmation failed: ' . $e->getMessage()], 500);
        }
    }

    public function refund(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $payment = Payment::findOrFail($request->payment_id);

        if ($payment->status !== 'succeeded') {
            return response()->json(['message' => 'Only succeeded payments can be refunded'], 422);
        }

        try {
            $refundAmount = $request->amount ? (int)($request->amount * 100) : null;

            $refundParams = [
                'payment_intent' => $payment->stripe_payment_intent_id,
            ];

            if ($refundAmount) {
                $refundParams['amount'] = $refundAmount;
            }

            $refund = Refund::create($refundParams);

            $refundedAmount = $refund->amount / 100;
            $isFullRefund = $refundedAmount >= $payment->amount;

            $payment->update([
                'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refund_amount' => ($payment->refund_amount ?? 0) + $refundedAmount,
                'refund_reason' => $request->reason,
            ]);

            if ($isFullRefund) {
                $payment->booking->update(['status' => 'cancelled']);
            }

            return response()->json([
                'message' => 'Refund processed successfully',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Refund failed: ' . $e->getMessage()], 500);
        }
    }

    public function invoice(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment->load(['booking.boat', 'user']);

        return response()->json([
            'invoice' => [
                'invoice_number' => 'INV-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'date' => $payment->created_at->format('Y-m-d'),
                'customer' => [
                    'name' => $payment->user->name,
                    'email' => $payment->user->email,
                ],
                'boat' => [
                    'name' => $payment->booking->boat->name,
                    'type' => $payment->booking->boat->type,
                ],
                'booking' => [
                    'start_date' => $payment->booking->start_date->format('Y-m-d H:i'),
                    'end_date' => $payment->booking->end_date->format('Y-m-d H:i'),
                    'duration_type' => $payment->booking->duration_type,
                    'duration_value' => $payment->booking->duration_value,
                ],
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'refund_amount' => $payment->refund_amount,
            ],
        ]);
    }
}
