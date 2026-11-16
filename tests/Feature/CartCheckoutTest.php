<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private int $variantSequence = 0;

    public function test_cart_checkout_and_order_pages_require_authentication(): void
    {
        $this->get(route('cart.index'))->assertRedirect(route('login'));
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_add_update_and_remove_a_cart_item_with_stock_limits(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 2);

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('cart.index', absolute: false))
            ->assertSessionHas('cart', [$variant->id => 2]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Rp20.000');

        $this->from(route('cart.index'))
            ->post(route('cart.items.store'), [
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('quantity')
            ->assertSessionHas('cart', [$variant->id => 2]);

        $this->patch(route('cart.items.update', $variant), ['quantity' => 1])
            ->assertRedirect(route('cart.index', absolute: false))
            ->assertSessionHas('cart', [$variant->id => 1]);

        $this->from(route('cart.index'))
            ->patch(route('cart.items.update', $variant), ['quantity' => 3])
            ->assertSessionHasErrors('quantity')
            ->assertSessionHas('cart', [$variant->id => 1]);

        $this->delete(route('cart.items.destroy', $variant->id))
            ->assertRedirect(route('cart.index', absolute: false))
            ->assertSessionMissing('cart');
    }

    public function test_checkout_creates_order_snapshots_reduces_stock_and_clears_the_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 4, price: '12500.00');
        $address = "Jl. <script>alert('x')</script>, Bandung";

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$variant->id => 2]])
            ->followingRedirects()
            ->post(route('checkout.store'), [
                'customer_name' => 'Pelanggan Uji',
                'customer_phone' => '08123456789',
                'shipping_address' => $address,
            ]);

        $order = Order::query()->whereBelongsTo($user)->firstOrFail();

        $response
            ->assertOk()
            ->assertSee('Kopi Uji')
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertSessionMissing('cart');

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('25000.00', $order->total_amount);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name_snapshot' => $variant->product->name,
            'variant_info_snapshot' => '200g - Whole Bean',
            'unit_price_snapshot' => '12500.00',
            'quantity' => 2,
            'subtotal' => '25000.00',
        ]);
        $this->assertSame(2, $variant->fresh()->stock);

        $variant->product->update(['name' => 'Nama produk yang diperbarui']);

        $this->get(route('orders.show', $order))
            ->assertSee('Kopi Uji')
            ->assertDontSee('Nama produk yang diperbarui');
    }

    public function test_checkout_keeps_the_cart_and_makes_no_order_when_any_item_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $availableVariant = $this->createVariant(stock: 5);
        $outOfStockVariant = $this->createVariant(stock: 0);
        $cart = [
            $availableVariant->id => 1,
            $outOfStockVariant->id => 1,
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'customer_name' => 'Pelanggan Uji',
                'customer_phone' => '08123456789',
                'shipping_address' => 'Jl. Kopi No. 1, Bandung',
            ])
            ->assertSessionHasErrors('cart')
            ->assertSessionHas('cart', $cart);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $availableVariant->fresh()->stock);
        $this->assertSame(0, $outOfStockVariant->fresh()->stock);
    }

    public function test_checkout_requires_shipping_details_and_preserves_the_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant(stock: 2);
        $cart = [$variant->id => 1];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'))
            ->assertSessionHasErrors([
                'customer_name',
                'customer_phone',
                'shipping_address',
            ])
            ->assertSessionHas('cart', $cart);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_user_can_only_view_their_own_orders(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownOrder = $this->createOrder($owner, 'NR-OWN-ORDER');
        $otherOrder = $this->createOrder($otherUser, 'NR-OTHER-ORDER');

        $this->actingAs($owner)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('NR-OWN-ORDER')
            ->assertDontSee('NR-OTHER-ORDER');

        $this->get(route('orders.show', $otherOrder))->assertNotFound();
        $this->get(route('orders.show', $ownOrder))->assertOk();
    }

    private function createVariant(int $stock, string $price = '10000.00'): ProductVariant
    {
        $sequence = ++$this->variantSequence;
        $product = Product::create([
            'name' => 'Kopi Uji',
            'slug' => 'kopi-uji-'.$sequence,
            'origin' => 'Aceh',
            'process' => 'Washed',
            'roast_level' => 'Medium',
            'is_active' => true,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-'.$sequence,
            'weight_grams' => 200,
            'grind_size' => 'Whole Bean',
            'price' => $price,
            'stock' => $stock,
        ]);
    }

    private function createOrder(User $user, string $orderNumber): Order
    {
        return $user->orders()->create([
            'order_number' => $orderNumber,
            'customer_name' => $user->name,
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Kopi No. 1, Bandung',
            'total_amount' => '10000.00',
            'status' => 'pending',
        ]);
    }
}
