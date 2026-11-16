@extends('layouts.store')

@section('title', $product->name . ' | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-stone-500" aria-label="Breadcrumb">
            <a href="{{ route('products.index') }}" class="transition hover:text-amber-800">Katalog</a>
            <span class="mx-2" aria-hidden="true">/</span>
            <span class="text-stone-800">{{ $product->name }}</span>
        </nav>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div
            x-data="{
                variants: @js($variantsData),
                selectedWeight: @js($weights->first()),
                selectedGrind: @js($grinds->first()),
                get selectedVariant() {
                    return this.variants.find(variant =>
                        Number(variant.weight_grams) === Number(this.selectedWeight) &&
                        variant.grind_size === this.selectedGrind
                    ) || null;
                },
                isAvailableGrind(grind) {
                    return this.variants.some(variant =>
                        Number(variant.weight_grams) === Number(this.selectedWeight) &&
                        variant.grind_size === grind
                    );
                },
                selectWeight(weight) {
                    this.selectedWeight = weight;

                    if (!this.isAvailableGrind(this.selectedGrind)) {
                        const firstVariant = this.variants.find(variant =>
                            Number(variant.weight_grams) === Number(weight)
                        );
                        this.selectedGrind = firstVariant ? firstVariant.grind_size : null;
                    }
                },
                formatPrice(price) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(price);
                },
                init() {
                    if (!this.selectedVariant && this.variants.length > 0) {
                        this.selectedWeight = this.variants[0].weight_grams;
                        this.selectedGrind = this.variants[0].grind_size;
                    }
                }
            }"
            class="grid gap-8 lg:grid-cols-2 lg:gap-14"
        >
            <div class="overflow-hidden rounded-3xl bg-stone-100">
                @if ($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="aspect-square h-full w-full object-cover">
                @else
                    <div class="flex aspect-square items-center justify-center text-stone-400">Foto kopi</div>
                @endif
            </div>

            <div class="py-2">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-amber-800">
                    {{ $product->origin ?: 'Kopi Nusantara' }}
                    @if ($product->process)
                        <span class="mx-1 text-stone-300">/</span>{{ $product->process }}
                    @endif
                </p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">{{ $product->name }}</h1>
                @if ($product->roast_level)
                    <span class="mt-4 inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-900">
                        {{ $product->roast_level }} roast
                    </span>
                @endif

                @if ($product->tasting_notes)
                    <div class="mt-7">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-500">Catatan rasa</h2>
                        <p class="mt-2 text-base leading-7 text-stone-800">{{ $product->tasting_notes }}</p>
                    </div>
                @endif

                @if ($product->description)
                    <div class="mt-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-500">Tentang kopi ini</h2>
                        <p class="mt-2 leading-7 text-stone-600">{{ $product->description }}</p>
                    </div>
                @endif

                <div class="mt-8 border-t border-stone-200 pt-6">
                    <div>
                        <h2 class="text-sm font-semibold text-stone-900">Pilih berat</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @forelse ($weights as $weight)
                                <button
                                    type="button"
                                    x-on:click="selectWeight({{ $weight }})"
                                    x-bind:aria-pressed="Number(selectedWeight) === {{ $weight }}"
                                    x-bind:class="Number(selectedWeight) === {{ $weight }} ? 'border-amber-800 bg-amber-800 text-white' : 'border-stone-300 bg-white text-stone-700 hover:border-amber-700'"
                                    class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2"
                                >
                                    {{ $weight }} g
                                </button>
                            @empty
                                <p class="text-sm text-stone-500">Varian berat belum tersedia.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-6">
                        <h2 class="text-sm font-semibold text-stone-900">Pilih ukuran gilingan</h2>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @forelse ($grinds as $grind)
                                <button
                                    type="button"
                                    x-on:click="selectedGrind = @js($grind)"
                                    x-bind:disabled="!isAvailableGrind(@js($grind))"
                                    x-bind:aria-pressed="selectedGrind === @js($grind)"
                                    x-bind:class="selectedGrind === @js($grind) ? 'border-amber-800 bg-amber-800 text-white' : 'border-stone-300 bg-white text-stone-700 hover:border-amber-700'"
                                    class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    {{ $grind }}
                                </button>
                            @empty
                                <p class="text-sm text-stone-500">Ukuran gilingan belum tersedia.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-7 rounded-2xl bg-stone-100 p-5" aria-live="polite">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="text-sm text-stone-500">Harga varian</p>
                            <p class="mt-1 text-3xl font-bold text-stone-950" x-text="selectedVariant ? formatPrice(selectedVariant.price) : 'Tidak tersedia'"></p>
                        </div>
                        <p class="text-sm text-stone-600">
                            Stok:
                            <span class="font-semibold text-stone-900" x-text="selectedVariant ? `${selectedVariant.stock} tersedia` : 'Tidak tersedia'"></span>
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    @auth
                        <form method="POST" action="{{ route('cart.items.store') }}">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <input
                                type="hidden"
                                name="product_variant_id"
                                x-bind:value="selectedVariant ? selectedVariant.id : ''"
                                x-bind:disabled="!selectedVariant || selectedVariant.stock < 1"
                            >
                            <button
                                type="submit"
                                x-bind:disabled="!selectedVariant || selectedVariant.stock < 1"
                                class="w-full rounded-xl bg-amber-800 px-6 py-3.5 text-center font-semibold text-white transition hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-stone-300"
                            >
                                Tambah ke Keranjang
                            </button>
                        </form>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="block w-full rounded-xl bg-amber-800 px-6 py-3.5 text-center font-semibold text-white transition hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2"
                        >
                            Masuk untuk Memesan
                        </a>
                    @endauth
                    <p class="mt-3 text-center text-xs text-stone-500">Pilih berat dan gilingan untuk melihat harga serta stok yang tersedia.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
