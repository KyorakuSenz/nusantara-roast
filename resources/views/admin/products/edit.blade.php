@extends('layouts.store')

@section('title', 'Edit ' . $product->name . ' | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-amber-800 hover:text-amber-950">&larr; Kembali ke produk</a>
        <div class="mt-5">
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Panel admin</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Edit produk</h1>
            <p class="mt-2 text-stone-600">{{ $product->name }}</p>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
        @endif

        @include('admin.products.form', [
            'action' => route('admin.products.update', $product),
            'method' => 'PUT',
            'submitLabel' => 'Simpan perubahan',
        ])
    </section>
@endsection
