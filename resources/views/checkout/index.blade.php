@extends('layouts.store')

@section('title', 'Checkout | Nusantara Roast')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-800">Satu langkah lagi</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-950">Checkout</h1>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_400px]">
            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
                @csrf
                <div>
                    <h2 class="text-lg font-bold text-stone-950">Informasi pengiriman</h2>
                    <p class="mt-1 text-sm text-stone-600">Pesanan akan dikirim ke alamat berikut.</p>
                </div>

                <div>
                    <label for="customer_name" class="block text-sm font-semibold text-stone-800">Nama penerima</label>
                    <input
                        id="customer_name"
                        name="customer_name"
                        type="text"
                        value="{{ old('customer_name', auth()->user()->name) }}"
                        autocomplete="name"
                        maxlength="255"
                        required
                        class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700"
                    >
                    @error('customer_name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="customer_phone" class="block text-sm font-semibold text-stone-800">Nomor telepon</label>
                    <input
                        id="customer_phone"
                        name="customer_phone"
                        type="tel"
                        value="{{ old('customer_phone') }}"
                        autocomplete="tel"
                        maxlength="30"
                        required
                        class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700"
                    >
                    @error('customer_phone')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="shipping_address" class="block text-sm font-semibold text-stone-800">Alamat lengkap</label>
                    <textarea
                        id="shipping_address"
                        name="shipping_address"
                        rows="4"
                        autocomplete="street-address"
                        maxlength="2000"
                        required
                        class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700"
                    >{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-stone-800">Catatan untuk pesanan <span class="font-normal text-stone-500">(opsional)</span></label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        maxlength="2000"
                        class="mt-2 w-full rounded-xl border-stone-300 focus:border-amber-700 focus:ring-amber-700"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    @disabled(! $canCheckout)
                    class="w-full rounded-xl bg-amber-800 px-5 py-3.5 font-semibold text-white transition hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-stone-300"
                >
                    Buat pesanan
                </button>
            </form>

            <aside class="h-fit rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-stone-950">Pesanan kamu</h2>
                <div class="mt-5 divide-y divide-stone-100">
                    @foreach ($items as $item)
                        <div class="py-4 first:pt-0 last:pb-0">
                            @if ($item['variant'])
                                <p class="font-semibold text-stone-900">{{ $item['variant']->product->name }}</p>
                                <p class="mt-1 text-sm text-stone-500">{{ $item['variant']->formatted_label }} &middot; {{ $item['quantity'] }} kemasan</p>
                                <div class="mt-2 flex items-center justify-between gap-4">
                                    <span class="text-sm text-stone-600">Rp{{ number_format((float) $item['variant']->price, 0, ',', '.') }} / kemasan</span>
                                    <span class="font-semibold text-stone-900">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                </div>
                                @if (! $item['available'])
                                    <p class="mt-2 text-sm font-medium text-red-700">Stok atau ketersediaan produk berubah. Perbarui keranjang sebelum memesan.</p>
                                @endif
                            @else
                                <p class="font-semibold text-red-800">Varian tidak lagi tersedia</p>
                                <p class="mt-1 text-sm text-stone-500">Hapus dari keranjang untuk melanjutkan.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-stone-200 pt-4">
                    <span class="font-semibold text-stone-700">Total</span>
                    <span class="text-xl font-bold text-stone-950">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <p class="mt-2 text-xs text-stone-500">Ongkos kirim akan dikonfirmasi terpisah.</p>
            </aside>
        </div>
    </section>
@endsection
