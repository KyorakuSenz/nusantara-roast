@extends('layouts.store')

@section('title', 'Katalog Kopi | Nusantara Roast')

@section('content')
    <section class="bg-amber-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">Dari kebun pilihan Indonesia</p>
            <h1 class="mt-3 max-w-2xl text-4xl font-bold tracking-tight sm:text-5xl">Temukan seduhan favoritmu.</h1>
            <p class="mt-4 max-w-xl text-base leading-7 text-amber-100">
                Jelajahi kopi spesialti dari berbagai daerah, dengan karakter rasa yang khas di setiap cangkir.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-labelledby="catalog-heading">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Koleksi kami</p>
                <h2 id="catalog-heading" class="mt-1 text-2xl font-bold tracking-tight text-stone-900">Katalog kopi</h2>
            </div>
            <p class="text-sm text-stone-500">{{ $products->count() }} kopi ditemukan</p>
        </div>

        <form method="GET" action="{{ route('products.index') }}" class="mt-6 grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_220px_200px_auto]">
            <label class="sr-only" for="search">Cari kopi</label>
            <input
                id="search"
                name="search"
                type="search"
                value="{{ request('search') }}"
                placeholder="Cari nama, asal, atau catatan rasa..."
                class="w-full rounded-xl border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700"
            >
            <label class="sr-only" for="origin">Filter asal kopi</label>
            <select id="origin" name="origin" class="w-full rounded-xl border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                <option value="">Semua origin</option>
                @foreach ($origins as $origin)
                    <option value="{{ $origin }}" @selected(request('origin') === $origin)>{{ $origin }}</option>
                @endforeach
            </select>
            <label class="sr-only" for="roast_level">Filter tingkat sangrai</label>
            <select id="roast_level" name="roast_level" class="w-full rounded-xl border-stone-300 text-sm focus:border-amber-700 focus:ring-amber-700">
                <option value="">Semua roast level</option>
                @foreach ($roastLevels as $roastLevel)
                    <option value="{{ $roastLevel }}" @selected(request('roast_level') === $roastLevel)>{{ $roastLevel }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-amber-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2">
                Cari kopi
            </button>
        </form>

        @if ($products->isNotEmpty())
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <article class="group overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <a href="{{ route('products.show', $product->slug) }}" class="block" aria-label="Lihat {{ $product->name }}">
                            <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
                                @if ($product->image_url)
                                    <img
                                        src="{{ $product->image_url }}"
                                        alt="{{ $product->name }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-stone-400">Foto kopi</div>
                                @endif
                                @if ($product->roast_level)
                                    <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-amber-900 shadow-sm">
                                        {{ $product->roast_level }} roast
                                    </span>
                                @endif
                            </div>
                            <div class="p-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-amber-800">{{ $product->origin ?: 'Nusantara' }}</p>
                                <h3 class="mt-2 text-lg font-bold text-stone-900 group-hover:text-amber-900">{{ $product->name }}</h3>
                                @if ($product->tasting_notes)
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-stone-600">{{ $product->tasting_notes }}</p>
                                @endif
                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-stone-100 pt-4">
                                    <div>
                                        <p class="text-xs text-stone-500">Harga mulai</p>
                                        @if ($product->variants->isNotEmpty())
                                            <p class="mt-0.5 font-bold text-stone-900">Rp{{ number_format((float) $product->variants->min('price'), 0, ',', '.') }}</p>
                                        @else
                                            <p class="mt-0.5 text-sm text-stone-500">Varian segera hadir</p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-amber-800">Lihat detail <span aria-hidden="true">&rarr;</span></span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">
                <h3 class="text-lg font-semibold text-stone-900">Kopi belum ditemukan</h3>
                <p class="mt-2 text-sm text-stone-600">Coba ubah kata pencarian atau filter yang kamu pilih.</p>
                <a href="{{ route('products.index') }}" class="mt-5 inline-flex rounded-xl bg-amber-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-900">
                    Hapus filter
                </a>
            </div>
        @endif
    </section>
@endsection
