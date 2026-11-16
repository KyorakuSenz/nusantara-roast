@extends('layouts.store')

@section('title', 'Pesanan ' . $order->order_number . ' | Admin')

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-amber-800 hover:text-amber-950">&larr; Kembali ke pesanan</a>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Detail pesanan</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">{{ $order->order_number }}</h1>
                <p class="mt-2 text-sm text-stone-600">{{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold capitalize text-amber-900">{{ $order->status }}</span>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-950">Item pesanan (snapshot)</h2>
                <div class="mt-4 divide-y divide-stone-100">
                    @forelse ($order->items as $item)
                        <div class="flex flex-wrap items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                            <div>
                                <p class="font-semibold text-stone-900">{{ $item->product_name_snapshot }}</p>
                                <p class="mt-1 text-sm text-stone-500">{{ $item->variant_info_snapshot }} &middot; {{ $item->quantity }} kemasan</p>
                                <p class="mt-1 text-sm text-stone-500">Rp{{ number_format((float) $item->unit_price_snapshot, 0, ',', '.') }} / kemasan</p>
                            </div>
                            <p class="font-bold text-stone-950">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="py-4 text-sm text-stone-500">Pesanan ini tidak memiliki item.</p>
                    @endforelse
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-stone-200 pt-4">
                    <span class="font-semibold text-stone-700">Total pesanan</span>
                    <span class="text-xl font-bold text-stone-950">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-6">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')
                    <label for="status" class="block text-sm font-semibold text-stone-800">Status pesanan</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="mt-4 w-full rounded-xl bg-amber-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-900">Perbarui status</button>
                </form>

                <aside class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-stone-950">Pelanggan & pengiriman</h2>
                    <p class="mt-4 font-semibold text-stone-900">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $order->user->email }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $order->customer_phone }}</p>
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $order->shipping_address }}</p>
                    @if ($order->notes)
                        <div class="mt-4 border-t border-stone-100 pt-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Catatan</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $order->notes }}</p>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>
@endsection
