@extends('layouts.store')

@section('title', 'Pesanan ' . $order->order_number . ' | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-amber-800 hover:text-amber-950">&larr; Kembali ke riwayat pesanan</a>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
        @endif

        <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Detail pesanan</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-950 sm:text-3xl">{{ $order->order_number }}</h1>
                <p class="mt-2 text-sm text-stone-600">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold capitalize text-amber-900">{{ $order->status }}</span>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-950">Item pesanan</h2>
                <div class="mt-4 divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <div class="flex flex-wrap items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                            <div>
                                <p class="font-semibold text-stone-900">{{ $item->product_name_snapshot }}</p>
                                <p class="mt-1 text-sm text-stone-500">{{ $item->variant_info_snapshot }} &middot; {{ $item->quantity }} kemasan</p>
                                <p class="mt-1 text-sm text-stone-500">Rp{{ number_format((float) $item->unit_price_snapshot, 0, ',', '.') }} / kemasan</p>
                            </div>
                            <p class="font-bold text-stone-950">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-stone-200 pt-4">
                    <span class="font-semibold text-stone-700">Total pesanan</span>
                    <span class="text-xl font-bold text-stone-950">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <aside class="h-fit rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-950">Alamat pengiriman</h2>
                <p class="mt-4 font-semibold text-stone-900">{{ $order->customer_name }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $order->customer_phone }}</p>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $order->shipping_address }}</p>
                @if ($order->notes)
                    <div class="mt-5 border-t border-stone-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Catatan</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $order->notes }}</p>
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection
