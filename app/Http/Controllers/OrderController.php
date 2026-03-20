<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class OrderController extends Controller
{
    // -----------------------------------------------------------------------
    // 1. Create a Stripe PaymentIntent
    //    Called via Axios from the Checkout page before the user confirms.
    // -----------------------------------------------------------------------

    public function createPaymentIntent(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Ostukorv on tühi.'], 422);
        }

        $total = (int) round(
            array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart)) * 100 // cents
        );

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount'                    => $total,
                'currency'                  => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata'                  => [
                    'user_id' => Auth::id() ?? 'guest',
                ],
            ]);

            return response()->json(['clientSecret' => $intent->client_secret]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe PaymentIntent creation failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Makse algatamine ebaõnnestus. Proovi uuesti.'], 500);
        }
    }

    // -----------------------------------------------------------------------
    // 2. Confirm order after successful client-side payment
    //    Called by the frontend after Stripe confirms the payment.
    // -----------------------------------------------------------------------

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|max:255',
            'phone'            => 'required|string|max:30',
            'payment_intent_id'=> 'required|string',
        ]);

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Ostukorv on tühi.'], 422);
        }

        // Verify the PaymentIntent with Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($data['payment_intent_id']);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => 'Makse kinnitamine ebaõnnestus.'], 422);
        }

        $paymentStatus = match ($intent->status) {
            'succeeded'               => 'paid',
            'processing'              => 'pending',
            'requires_payment_method' => 'failed',
            default                   => 'failed',
        };

        $total = round(
            array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart)),
            2
        );

        $order = Order::create([
            'user_id'           => Auth::id(),
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'total'             => $total,
            'payment_provider'  => 'stripe',
            'payment_intent_id' => $data['payment_intent_id'],
            'payment_status'    => $paymentStatus,
            'items'             => array_values($cart),
        ]);

        // Only clear the cart on definite success
        if ($paymentStatus === 'paid') {
            $request->session()->forget('cart');
        }

        return response()->json([
            'order_id'       => $order->id,
            'payment_status' => $paymentStatus,
        ]);
    }

    // -----------------------------------------------------------------------
    // 3. Order confirmation page
    // -----------------------------------------------------------------------

    public function confirmation(Request $request, Order $order)
    {
        // Only the buyer (or admin) may see the confirmation
        if (Auth::id() && $order->user_id && $order->user_id !== Auth::id() && Auth::id() !== 1) {
            abort(403);
        }

        return Inertia::render('Shop/Confirmation', [
            'order' => $order,
        ]);
    }

    // -----------------------------------------------------------------------
    // 4. Stripe Webhook  (POST /stripe/webhook)
    //    Handles asynchronous status updates from Stripe.
    //    Add this endpoint to your Stripe dashboard webhook settings.
    // -----------------------------------------------------------------------

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        $intent = $event->data->object;

        $statusMap = [
            'payment_intent.succeeded'               => 'paid',
            'payment_intent.payment_failed'          => 'failed',
            'payment_intent.processing'              => 'pending',
            'payment_intent.canceled'                => 'failed',
        ];

        if (isset($statusMap[$event->type])) {
            Order::where('payment_intent_id', $intent->id)
                ->update(['payment_status' => $statusMap[$event->type]]);
        }

        return response('OK', 200);
    }
}