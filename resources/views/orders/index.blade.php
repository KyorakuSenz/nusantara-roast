@extends('layouts.store')

@section('title', 'Riwayat Pesanan | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Perjalanan kopimu</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Riwayat pesanan</h1>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
        @endif

        @if ($orders->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-14 text-center">
                <h2 class="text-xl font-semibold text-stone-900">Belum ada pesanan</h2>
                <p class="mt-2 text-sm text-stone-600">Pesanan yang kamu buat akan muncul di sini.</p>
                <a href="{{ route('products.index') }}" class="mt-5 inline-flex rounded-xl bg-amber-800 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-900">Lihat katalog</a>
            </div>
        @else
            <div class="mt-8 space-y-4">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="block rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-amber-300 hover:shadow-md sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Nomor pesanan</p>
                                <p class="mt-1 font-bold text-stone-950">{{ $order->order_number }}</p>
                                <p class="mt-2 text-sm text-stone-600">{{ $order->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold capitalize text-amber-900">{{ $order->status }}</span>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-4">
                            <span class="text-sm text-stone-600">{{ $order->customer_name }}</span>
                            <span class="font-bold text-stone-950">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
