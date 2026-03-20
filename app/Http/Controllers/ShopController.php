<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    /**
     * Products listing page.
     * Also passes the current cart and total so the frontend can show the badge.
     */
    public function index(Request $request)
    {
        $products = Product::where('in_stock', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $cart  = array_values($request->session()->get('cart', []));
        $total = round(
            array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart)),
            2
        );

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'cart'     => $cart,
            'total'    => $total,
        ]);
    }

    /**
     * Checkout page — requires a non-empty cart.
     */
    public function checkout(Request $request)
    {
        $cart = array_values($request->session()->get('cart', []));

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Ostukorv on tühi.');
        }

        $total = round(
            array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart)),
            2
        );

        return Inertia::render('Shop/Checkout', [
            'cart'            => $cart,
            'total'           => $total,
            // Pass the publishable key to the frontend so Stripe.js can be initialised
            'stripePublicKey' => config('services.stripe.key'),
        ]);
    }
}