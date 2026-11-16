<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        $cart = is_array($cart) ? $cart : [];

        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Keranjang kamu masih kosong.',
            ]);
        }

        $variants = ProductVariant::with('product')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect($cart)->map(function (int|string $quantity, int|string $variantId) use ($variants): array {
            $variant = $variants->get((int) $variantId);
            $quantity = max(0, (int) $quantity);

            return [
                'variant' => $variant,
                'quantity' => $quantity,
                'subtotal' => $variant ? round((float) $variant->price * $quantity, 2) : 0,
                'available' => $variant !== null && $variant->product->is_active && $quantity > 0 && $quantity <= $variant->stock,
            ];
        });

        $subtotal = round($items->sum('subtotal'), 2);
        $canCheckout = $items->isNotEmpty() && $items->every(fn (array $item): bool => $item['available']);

        return view('checkout.index', compact('items', 'subtotal', 'canCheckout'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $cart = $request->session()->get('cart', []);
        $cart = is_array($cart) ? $cart : [];

        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Keranjang kamu masih kosong.',
            ]);
        }

        $quantities = [];

        foreach ($cart as $variantId => $quantity) {
            if (! ctype_digit((string) $variantId) || ! filter_var($quantity, FILTER_VALIDATE_INT) || (int) $quantity < 1) {
                throw ValidationException::withMessages([
                    'cart' => 'Isi keranjang tidak valid. Silakan periksa kembali keranjang kamu.',
                ]);
            }

            $quantities[(int) $variantId] = (int) $quantity;
        }

        $order = DB::transaction(function () use ($request, $validated, $quantities): Order {
            $variants = ProductVariant::with('product')
                ->whereIn('id', array_keys($quantities))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($variants->count() !== count($quantities)) {
                throw ValidationException::withMessages([
                    'cart' => 'Salah satu produk di keranjang sudah tidak tersedia. Perbarui keranjang kamu.',
                ]);
            }

            foreach ($quantities as $variantId => $quantity) {
                $variant = $variants->get($variantId);

                if (! $variant->product->is_active || $quantity > $variant->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$variant->formatted_label} tidak lagi mencukupi. Perbarui keranjang kamu.",
                    ]);
                }
            }

            $totalAmount = 0;

            foreach ($quantities as $variantId => $quantity) {
                $totalAmount += (float) $variants->get($variantId)->price * $quantity;
            }

            $order = $request->user()->orders()->create([
                'order_number' => 'NR-'.Str::upper((string) Str::ulid()),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'total_amount' => round($totalAmount, 2),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($quantities as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                $unitPrice = (float) $variant->price;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->product->name,
                    'variant_info_snapshot' => $variant->formatted_label,
                    'unit_price_snapshot' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => round($unitPrice * $quantity, 2),
                ]);

                $variant->decrement('stock', $quantity);
            }

            return $order;
        }, attempts: 3);

        $request->session()->forget('cart');

        return redirect()->route('orders.show', $order)->with('status', 'Pesanan berhasil dibuat.');
    }
}
