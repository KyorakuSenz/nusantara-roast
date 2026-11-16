@extends('layouts.store')

@section('title', 'Pesanan Masuk | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Panel admin</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Pesanan masuk</h1>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                    <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                        <tr>
                            <th scope="col" class="px-5 py-4 font-semibold">Pesanan</th>
                            <th scope="col" class="px-5 py-4 font-semibold">Pelanggan</th>
                            <th scope="col" class="px-5 py-4 font-semibold">Item</th>
                            <th scope="col" class="px-5 py-4 font-semibold">Total</th>
                            <th scope="col" class="px-5 py-4 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-4 text-right font-semibold">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-stone-900">{{ $order->order_number }}</p>
                                    <p class="mt-1 text-xs text-stone-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-stone-900">{{ $order->customer_name }}</p>
                                    <p class="mt-1 text-xs text-stone-500">{{ $order->user->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-stone-600">{{ $order->items_count }}</td>
                                <td class="px-5 py-4 font-semibold text-stone-900">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold capitalize text-amber-900">{{ $order->status }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-amber-800 hover:text-amber-950">Lihat pesanan</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-stone-500">Belum ada pesanan masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </section>
@endsection
