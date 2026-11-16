<form
    method="POST"
    action="{{ $action }}"
    x-data="{ variants: @js(old('variants', $formVariants)) }"
    class="mt-8 space-y-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <p class="font-semibold">Periksa kembali data produk:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <h2 class="text-lg font-bold text-stone-950">Informasi produk</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-semibold text-stone-800">Nama produk</label>
                <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" maxlength="255" required class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-semibold text-stone-800">Slug</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug', $product->slug) }}" maxlength="255" required class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                @error('slug')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="origin" class="block text-sm font-semibold text-stone-800">Origin</label>
                <input id="origin" name="origin" type="text" value="{{ old('origin', $product->origin) }}" maxlength="100" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                @error('origin')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="process" class="block text-sm font-semibold text-stone-800">Proses</label>
                <input id="process" name="process" type="text" value="{{ old('process', $product->process) }}" maxlength="100" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                @error('process')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="roast_level" class="block text-sm font-semibold text-stone-800">Roast level</label>
                <select id="roast_level" name="roast_level" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                    <option value="">Pilih roast level</option>
                    @foreach (['Light', 'Medium', 'Dark'] as $roastLevel)
                        <option value="{{ $roastLevel }}" @selected(old('roast_level', $product->roast_level) === $roastLevel)>{{ $roastLevel }}</option>
                    @endforeach
                </select>
                @error('roast_level')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="image_url" class="block text-sm font-semibold text-stone-800">URL gambar</label>
                <input id="image_url" name="image_url" type="url" value="{{ old('image_url', $product->image_url) }}" maxlength="255" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                @error('image_url')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="tasting_notes" class="block text-sm font-semibold text-stone-800">Catatan rasa</label>
                <textarea id="tasting_notes" name="tasting_notes" rows="2" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">{{ old('tasting_notes', $product->tasting_notes) }}</textarea>
                @error('tasting_notes')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="block text-sm font-semibold text-stone-800">Deskripsi</label>
                <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">{{ old('description', $product->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <label for="is_active" class="inline-flex items-center gap-3 text-sm font-semibold text-stone-800">
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $product->exists ? $product->is_active : true)) class="rounded border-stone-300 text-amber-800 focus:ring-amber-700">
                    Tampilkan produk di katalog
                </label>
                @error('is_active')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="border-t border-stone-200 pt-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-stone-950">Varian produk</h2>
                <p class="mt-1 text-sm text-stone-600">Setiap kombinasi berat dan gilingan harus unik.</p>
            </div>
            <button type="button" x-on:click="variants.push({ sku: '', weight_grams: 200, grind_size: 'Whole Bean', price: '', stock: 0 })" class="rounded-xl border border-amber-800 px-4 py-2.5 text-sm font-semibold text-amber-900 transition hover:bg-amber-50">
                Tambah varian
            </button>
        </div>

        <div class="mt-5 space-y-4">
            <template x-for="(variant, index) in variants" :key="variant.id || index">
                <fieldset class="rounded-xl border border-stone-200 p-4">
                    <legend class="px-2 text-sm font-semibold text-stone-700" x-text="`Varian ${index + 1}`"></legend>
                    @if ($product->exists)
                        <input type="hidden" x-bind:name="'variants[' + index + '][id]'" x-bind:value="variant.id || ''">
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="block text-xs font-semibold text-stone-600">SKU</label>
                            <input type="text" x-model="variant.sku" x-bind:name="'variants[' + index + '][sku]'" maxlength="100" required class="mt-1.5 w-full rounded-lg border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-600">Berat (gram)</label>
                            <input type="number" x-model.number="variant.weight_grams" x-bind:name="'variants[' + index + '][weight_grams]'" min="1" step="1" required class="mt-1.5 w-full rounded-lg border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-600">Grind size</label>
                            <input type="text" x-model="variant.grind_size" x-bind:name="'variants[' + index + '][grind_size]'" maxlength="50" required class="mt-1.5 w-full rounded-lg border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-600">Harga (Rp)</label>
                            <input type="number" x-model="variant.price" x-bind:name="'variants[' + index + '][price]'" min="0.01" step="0.01" required class="mt-1.5 w-full rounded-lg border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-600">Stok</label>
                            <input type="number" x-model.number="variant.stock" x-bind:name="'variants[' + index + '][stock]'" min="0" step="1" required class="mt-1.5 w-full rounded-lg border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                        </div>
                    </div>
                    <button type="button" x-on:click="variants.splice(index, 1)" class="mt-4 text-sm font-semibold text-red-700 hover:text-red-900">Hapus varian</button>
                </fieldset>
            </template>
        </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3 border-t border-stone-200 pt-6">
        <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">Batal</a>
        <button type="submit" class="rounded-xl bg-amber-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-900">{{ $submitLabel }}</button>
    </div>
</form>
