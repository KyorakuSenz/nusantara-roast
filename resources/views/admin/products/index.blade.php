@extends('layouts.store')

@section('title', 'Kelola Produk | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Panel admin</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Kelola produk kopi</h1>
            </div>
            <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-amber-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-900">Tambah produk</a>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">
                <h2 class="text-xl font-semibold text-stone-900">Belum ada produk kopi</h2>
                <p class="mt-2 text-sm text-stone-600">Buat produk pertama untuk ditampilkan di katalog.</p>
            </div>
        @else
            <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                        <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                            <tr>
                                <th scope="col" class="px-5 py-4 font-semibold">Produk</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Origin</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Roast</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Varian</th>
                                <th scope="col" class="px-5 py-4 font-semibold">Status</th>
                                <th scope="col" class="px-5 py-4 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($products as $product)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-stone-900">{{ $product->name }}</p>
                                        <p class="mt-1 text-xs text-stone-500">{{ $product->slug }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-stone-600">{{ $product->origin ?: '—' }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $product->roast_level ?: '—' }}</td>
                                    <td class="px-5 py-4 text-stone-600">{{ $product->variants_count }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-stone-600' }}">
                                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-amber-800 hover:text-amber-950">Edit</a>
                                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk dan semua variannya?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-700 hover:text-red-900">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
