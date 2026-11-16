@extends('layouts.store')

@section('title', 'Keranjang | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Sebelum diseduh</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Keranjang belanja</h1>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-amber-800 hover:text-amber-950">Lanjut belanja <span aria-hidden="true">&rarr;</span></a>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">
                <h2 class="text-xl font-semibold text-stone-900">Keranjangmu masih kosong</h2>
                <p class="mt-2 text-sm text-stone-600">Pilih kopi favoritmu untuk mulai memesan.</p>
                <a href="{{ route('products.index') }}" class="mt-5 inline-flex rounded-xl bg-amber-800 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-900">Jelajahi katalog</a>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div class="space-y-4">
                    @foreach ($items as $variantId => $item)
                        @php($variant = $item['variant'])
                        <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    @if ($variant)
                                        <a href="{{ route('products.show', $variant->product->slug) }}" class="font-bold text-stone-900 hover:text-amber-900">{{ $variant->product->name }}</a>
                                        <p class="mt-1 text-sm text-stone-600">{{ $variant->formatted_label }}</p>
                                        <p class="mt-2 text-sm font-semibold text-stone-900">Rp{{ number_format((float) $variant->price, 0, ',', '.') }} / kemasan</p>
                                        <p class="mt-1 text-xs {{ $item['quantity'] > $variant->stock ? 'font-semibold text-red-700' : 'text-stone-500' }}">
                                            Stok tersedia: {{ $variant->stock }}
                                            @if ($item['quantity'] > $variant->stock)
                                                — jumlah di keranjang melebihi stok
                                            @endif
                                        </p>
                                    @else
                                        <p class="font-semibold text-red-800">Varian tidak lagi tersedia</p>
                                        <p class="mt-1 text-sm text-stone-600">Hapus produk ini dari keranjang untuk melanjutkan.</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-4">
                                    @if ($variant)
                                        <form method="POST" action="{{ route('cart.items.update', $variant->id) }}" x-data="{ quantity: {{ $item['quantity'] }}, max: {{ $variant->stock }} }" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="button"
                                                x-on:click="quantity = Math.max(1, quantity - 1)"
                                                x-bind:disabled="quantity <= 1"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-stone-300 text-lg font-semibold text-stone-700 hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
                                                aria-label="Kurangi jumlah"
                                            >−</button>
                                            <input
                                                type="number"
                                                name="quantity"
                                                min="1"
                                                max="{{ $variant->stock }}"
                                                x-model.number="quantity"
                                                class="w-16 rounded-lg border-stone-300 px-2 py-2 text-center text-sm focus:border-amber-700 focus:ring-amber-700"
                                                aria-label="Jumlah kemasan"
                                                required
                                            >
                                            <button
                                                type="button"
                                                x-on:click="quantity = Math.min(max, quantity + 1)"
                                                x-bind:disabled="quantity >= max"
                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-stone-300 text-lg font-semibold text-stone-700 hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
                                                aria-label="Tambah jumlah"
                                            >+</button>
                                            <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-50">Perbarui</button>
                                        </form>
                                        <p class="w-full text-right font-bold text-stone-950 sm:w-auto">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                    @endif

                                    <form method="POST" action="{{ route('cart.items.destroy', $variantId) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-900">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="h-fit rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-stone-950">Ringkasan belanja</h2>
                    <div class="mt-5 flex items-center justify-between border-t border-stone-200 pt-4">
                        <span class="text-sm text-stone-600">Subtotal</span>
                        <span class="text-xl font-bold text-stone-950">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-2 text-xs text-stone-500">Ongkos kirim dikonfirmasi terpisah.</p>
                    <a href="{{ route('checkout.index') }}" class="mt-6 block rounded-xl bg-amber-800 px-5 py-3 text-center font-semibold text-white transition hover:bg-amber-900">Lanjut ke checkout</a>
                </aside>
            </div>
        @endif
    </section>
@endsection
