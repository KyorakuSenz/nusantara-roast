<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const PRODUCT_FIELDS = [
        'name',
        'slug',
        'origin',
        'process',
        'roast_level',
        'tasting_notes',
        'description',
        'image_url',
        'is_active',
    ];

    private const VARIANT_FIELDS = [
        'sku',
        'weight_grams',
        'grind_size',
        'price',
        'stock',
    ];

    public function index(): View
    {
        $products = Product::withCount('variants')
            ->latest()
            ->paginate(12);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $product = new Product;
        $formVariants = [[
            'sku' => '',
            'weight_grams' => 200,
            'grind_size' => 'Whole Bean',
            'price' => '',
            'stock' => 0,
        ]];

        return view('admin.products.create', compact('product', 'formVariants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $product = DB::transaction(function () use ($validated): Product {
            $product = Product::create(Arr::only($validated, self::PRODUCT_FIELDS));
            $this->syncVariants($product, $validated['variants']);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)
            ->with('status', 'Produk dan variannya berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $product->load('variants');
        $formVariants = $product->variants->map(fn ($variant): array => [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'weight_grams' => $variant->weight_grams,
            'grind_size' => $variant->grind_size,
            'price' => $variant->price,
            'stock' => $variant->stock,
        ])->all();

        return view('admin.products.edit', compact('product', 'formVariants'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        DB::transaction(function () use ($product, $validated): void {
            $product->update(Arr::only($validated, self::PRODUCT_FIELDS));
            $this->syncVariants($product, $validated['variants']);
        });

        return redirect()->route('admin.products.edit', $product)
            ->with('status', 'Produk dan variannya berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Produk berhasil dihapus.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $variantRows = $request->input('variants', []);
        $variantRows = is_array($variantRows) ? $variantRows : [];
        $existingVariantIds = $product
            ? $product->variants()->pluck('id')->map(fn ($id): int => (int) $id)->all()
            : [];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)],
            'origin' => ['nullable', 'string', 'max:100'],
            'process' => ['nullable', 'string', 'max:100'],
            'roast_level' => ['nullable', 'string', 'max:50'],
            'tasting_notes' => ['nullable', 'string', 'max:5000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'variants' => ['required', 'array', 'list', 'min:1'],
            'variants.*' => ['required', 'array:id,sku,weight_grams,grind_size,price,stock'],
        ];

        foreach ($variantRows as $index => $variantRow) {
            if (! is_int($index) || ! is_array($variantRow)) {
                continue;
            }

            $variantPath = "variants.{$index}";
            $variantId = $variantRow['id'] ?? null;
            $ignoredVariantId = filter_var($variantId, FILTER_VALIDATE_INT);
            $ignoredVariantId = $ignoredVariantId !== false && in_array((int) $ignoredVariantId, $existingVariantIds, true)
                ? (int) $ignoredVariantId
                : null;

            $rules["{$variantPath}.id"] = $product
                ? ['nullable', 'integer', Rule::exists('product_variants', 'id')->where('product_id', $product->id)]
                : ['prohibited'];
            $rules["{$variantPath}.sku"] = [
                'required',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')->ignore($ignoredVariantId),
            ];
            $rules["{$variantPath}.weight_grams"] = ['required', 'integer', 'min:1'];
            $rules["{$variantPath}.grind_size"] = ['required', 'string', 'max:50'];
            $rules["{$variantPath}.price"] = ['required', 'numeric', 'min:0.01'];
            $rules["{$variantPath}.stock"] = ['required', 'integer', 'min:0'];
        }

        $validated = $request->validate($rules);
        $combinations = [];
        $variantIds = [];
        $errors = [];

        foreach ($validated['variants'] as $index => $variant) {
            $combination = $variant['weight_grams'].'|'.mb_strtolower(trim($variant['grind_size']));

            if (isset($combinations[$combination])) {
                $errors["variants.{$index}.grind_size"] = 'Kombinasi berat dan gilingan tidak boleh duplikat.';
            }

            $combinations[$combination] = true;

            if (! empty($variant['id'])) {
                $variantIds[] = (int) $variant['id'];
            }
        }

        if (count($variantIds) !== count(array_unique($variantIds))) {
            $errors['variants'] = 'Varian yang sama tidak boleh dikirim lebih dari satu kali.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $submittedIds = [];

        foreach ($variants as $variantData) {
            $attributes = Arr::only($variantData, self::VARIANT_FIELDS);

            if (! empty($variantData['id'])) {
                $variant = $product->variants()->whereKey($variantData['id'])->firstOrFail();
                $variant->update($attributes);
            } else {
                $variant = $product->variants()->create($attributes);
            }

            $submittedIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $submittedIds)->delete();
    }
}
