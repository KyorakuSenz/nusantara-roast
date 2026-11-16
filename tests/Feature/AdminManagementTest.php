<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authentication_and_reject_customers(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_products_with_variants(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Tambah produk kopi');

        $response = $this
            ->post(route('admin.products.store'), $this->productInput('Kopi Uji', 'kopi-uji', [
                $this->variantInput('UJI-200-WB', 200, 'Whole Bean', '95000.00', 10),
                $this->variantInput('UJI-500-FINE', 500, 'Fine', '210000.00', 5),
            ]));

        $product = Product::query()->where('slug', 'kopi-uji')->firstOrFail();
        $response->assertRedirect(route('admin.products.edit', $product, absolute: false));

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('UJI-200-WB');
        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Kopi Uji');

        $originalVariants = $product->variants()->get()->keyBy('sku');
        $variantToKeep = $originalVariants->get('UJI-200-WB');
        $variantToRemove = $originalVariants->get('UJI-500-FINE');

        $this->put(route('admin.products.update', $product), $this->productInput('Kopi Uji Baru', 'kopi-uji-baru', [
            [
                'id' => $variantToKeep->id,
                ...$this->variantInput('UJI-200-WB', 200, 'Whole Bean', '99000.00', 8),
            ],
            $this->variantInput('UJI-1000-COARSE', 1000, 'Coarse', '390000.00', 3),
        ]))
            ->assertRedirect(route('admin.products.edit', $product, absolute: false));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Kopi Uji Baru',
            'slug' => 'kopi-uji-baru',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variantToKeep->id,
            'price' => '99000.00',
            'stock' => 8,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'UJI-1000-COARSE',
        ]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variantToRemove->id]);
        $this->assertDatabaseCount('product_variants', 2);

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index', absolute: false));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_product_variant_weight_and_grind_combinations_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $this->productInput('Kopi Duplikat', 'kopi-duplikat', [
                $this->variantInput('DUP-1', 200, 'Whole Bean', '95000.00', 4),
                $this->variantInput('DUP-2', 200, 'whole bean', '96000.00', 4),
            ]))
            ->assertSessionHasErrors('variants.1.grind_size');

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_variants', 0);
    }

    public function test_admin_can_review_order_snapshots_and_update_only_to_supported_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $customer->orders()->create([
            'order_number' => 'NR-ADMIN-ORDER',
            'customer_name' => 'Pelanggan Uji',
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Kopi No. 1, Bandung',
            'total_amount' => '190000.00',
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_name_snapshot' => 'Aceh Gayo Snapshot',
            'variant_info_snapshot' => '500g - Whole Bean',
            'unit_price_snapshot' => '95000.00',
            'quantity' => 2,
            'subtotal' => '190000.00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('NR-ADMIN-ORDER')
            ->assertSee('Pelanggan Uji');

        $this->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Aceh Gayo Snapshot')
            ->assertSee('500g - Whole Bean');

        $this->patch(route('admin.orders.update', $order), ['status' => 'shipped'])
            ->assertRedirect(route('admin.orders.show', $order, absolute: false));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);

        $this->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), ['status' => 'refunded'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     * @return array<string, mixed>
     */
    private function productInput(string $name, string $slug, array $variants): array
    {
        return [
            'name' => $name,
            'slug' => $slug,
            'origin' => 'Aceh',
            'process' => 'Washed',
            'roast_level' => 'Medium',
            'tasting_notes' => 'Cokelat dan buah',
            'description' => 'Kopi uji untuk panel admin.',
            'image_url' => null,
            'is_active' => 1,
            'variants' => $variants,
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function variantInput(string $sku, int $weight, string $grind, string $price, int $stock): array
    {
        return [
            'sku' => $sku,
            'weight_grams' => $weight,
            'grind_size' => $grind,
            'price' => $price,
            'stock' => $stock,
        ];
    }
}
