<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Manages a session-based shopping cart.
 *
 * Cart structure stored in session under key 'cart':
 *   [
 *     product_id => [
 *       'id'        => int,
 *       'name'      => string,
 *       'price'     => float,
 *       'image_url' => string,
 *       'quantity'  => int,
 *     ],
 *     ...
 *   ]
 */
class CartController extends Controller
{
    // -----------------------------------------------------------------------
    // Read
    // -----------------------------------------------------------------------

    /**
     * Return the current cart as JSON (used by the Vue frontend via Inertia prop
     * or direct Axios call).
     */
    public function index(Request $request)
    {
        return response()->json([
            'cart'  => array_values($request->session()->get('cart', [])),
            'total' => $this->cartTotal($request),
        ]);
    }

    // -----------------------------------------------------------------------
    // Write
    // -----------------------------------------------------------------------

    /**
     * Add a product to the cart (or increment quantity if already present).
     */
    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'integer|min:1|max:99',
        ]);

        $qty     = $data['quantity'] ?? 1;
        $product = Product::findOrFail($data['product_id']);
        $cart    = $request->session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $qty;
        } else {
            $cart[$product->id] = [
                'id'        => $product->id,
                'name'      => $product->name,
                'price'     => $product->price,
                'image_url' => $product->image_url,
                'quantity'  => $qty,
            ];
        }

        $request->session()->put('cart', $cart);

        return response()->json([
            'cart'    => array_values($cart),
            'total'   => $this->cartTotal($request, $cart),
            'message' => 'Toode lisatud ostukorvi.',
        ]);
    }

    /**
     * Update the quantity of a specific cart item.
     * Passing quantity 0 removes the item.
     */
    public function update(Request $request, int $productId)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = $request->session()->get('cart', []);

        if ($data['quantity'] === 0) {
            unset($cart[$productId]);
        } elseif (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $data['quantity'];
        }

        $request->session()->put('cart', $cart);

        return response()->json([
            'cart'  => array_values($cart),
            'total' => $this->cartTotal($request, $cart),
        ]);
    }

    /**
     * Remove a specific product from the cart.
     */
    public function remove(Request $request, int $productId)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$productId]);
        $request->session()->put('cart', $cart);

        return response()->json([
            'cart'    => array_values($cart),
            'total'   => $this->cartTotal($request, $cart),
            'message' => 'Toode eemaldatud ostukorvist.',
        ]);
    }

    /**
     * Empty the entire cart.
     */
    public function clear(Request $request)
    {
        $request->session()->forget('cart');

        return response()->json(['cart' => [], 'total' => 0]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function cartTotal(Request $request, ?array $cart = null): float
    {
        $cart ??= $request->session()->get('cart', []);

        return round(
            array_sum(array_map(
                fn ($item) => $item['price'] * $item['quantity'],
                $cart
            )),
            2
        );
    }
}