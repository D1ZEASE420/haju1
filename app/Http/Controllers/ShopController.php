<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShopController extends Controller
{
    /**
     * Products listing page.
     */
    public function index(Request $request)
    {
        $products = Product::orderBy('category')->orderBy('name')->get();

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
     * Show create product form — admin only.
     */
    public function create()
    {
        abort_if(!Auth::user()?->isAdmin(), 403);

        return Inertia::render('Shop/ProductForm');
    }

    /**
     * Store new product — admin only.
     */
    public function storeProduct(Request $request)
    {
        abort_if(!Auth::user()?->isAdmin(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'image_url'   => 'nullable|url|max:500',
            'category'    => 'required|string|max:100',
            'in_stock'    => 'boolean',
        ]);

        $data['in_stock'] = $data['in_stock'] ?? true;

        Product::create($data);

        return redirect()->route('shop.index')->with('success', 'Toode lisatud!');
    }

    /**
     * Show edit product form — admin only.
     */
    public function editProduct(Product $product)
    {
        abort_if(!Auth::user()?->isAdmin(), 403);

        return Inertia::render('Shop/ProductForm', [
            'product' => $product,
        ]);
    }

    /**
     * Update product — admin only.
     */
    public function updateProduct(Request $request, Product $product)
    {
        abort_if(!Auth::user()?->isAdmin(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'image_url'   => 'nullable|url|max:500',
            'category'    => 'required|string|max:100',
            'in_stock'    => 'boolean',
        ]);

        $product->update($data);

        return redirect()->route('shop.index')->with('success', 'Toode uuendatud!');
    }

    /**
     * Delete product — admin only.
     */
    public function destroyProduct(Product $product)
    {
        abort_if(!Auth::user()?->isAdmin(), 403);

        $product->delete();

        return redirect()->route('shop.index')->with('success', 'Toode kustutatud!');
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
            'stripePublicKey' => config('services.stripe.key'),
        ]);
    }
}