<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $cart = is_array($cart) ? $cart : [];

        $variants = ProductVariant::with('product')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect($cart)->map(function (int|string $quantity, int|string $variantId) use ($variants): array {
            $variant = $variants->get((int) $variantId);

            return [
                'variant' => $variant,
                'quantity' => max(0, (int) $quantity),
                'subtotal' => $variant ? round((float) $variant->price * (int) $quantity, 2) : 0,
            ];
        });

        $subtotal = round($items->sum('subtotal'), 2);

        return view('cart.index', compact('items', 'subtotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::with('product')
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->findOrFail($validated['product_variant_id']);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $cart = $request->session()->get('cart', []);
        $cart = is_array($cart) ? $cart : [];
        $newQuantity = (int) ($cart[$variant->id] ?? 0) + $quantity;

        if ($newQuantity > $variant->stock) {
            return back()->withErrors([
                'quantity' => "Stok {$variant->formatted_label} hanya {$variant->stock} tersedia.",
            ]);
        }

        $cart[$variant->id] = $newQuantity;
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Kopi berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->session()->get('cart', []);
        $cart = is_array($cart) ? $cart : [];

        if (! array_key_exists($variant->id, $cart)) {
            abort(404);
        }

        $quantity = (int) $validated['quantity'];

        if ($quantity > $variant->stock) {
            return back()->withErrors([
                'quantity' => "Stok {$variant->formatted_label} hanya {$variant->stock} tersedia.",
            ]);
        }

        $cart[$variant->id] = $quantity;
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('status', 'Jumlah produk berhasil diperbarui.');
    }

    public function destroy(Request $request, string $variantId): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        $cart = is_array($cart) ? $cart : [];
        unset($cart[$variantId]);

        if ($cart === []) {
            $request->session()->forget('cart');
        } else {
            $request->session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('status', 'Produk dihapus dari keranjang.');
    }
}
