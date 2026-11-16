@extends('layouts.store')

@section('title', 'Tambah Produk | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-amber-800 hover:text-amber-950">&larr; Kembali ke produk</a>
        <div class="mt-5">
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Panel admin</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Tambah produk kopi</h1>
        </div>

        @include('admin.products.form', [
            'action' => route('admin.products.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan produk',
        ])
    </section>
@endsection
