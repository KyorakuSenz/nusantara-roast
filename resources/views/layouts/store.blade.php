<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Nusantara Roast'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="text-xl font-bold tracking-tight text-amber-950">
                Nusantara <span class="text-amber-700">Roast</span>
            </a>
            <div class="flex items-center gap-3 text-sm font-medium">
                <a href="{{ route('products.index') }}" class="text-stone-600 transition hover:text-amber-800">Katalog</a>
                @auth
                    <a href="{{ route('cart.index') }}" class="text-stone-600 transition hover:text-amber-800">Keranjang</a>
                    <a href="{{ route('orders.index') }}" class="text-stone-600 transition hover:text-amber-800">Pesanan</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.products.index') }}" class="text-stone-600 transition hover:text-amber-800">Admin</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="rounded-full bg-stone-100 px-4 py-2 text-stone-700 transition hover:bg-stone-200">
                        {{ auth()->user()->name }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full bg-amber-800 px-4 py-2 text-white transition hover:bg-amber-900">Masuk</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-stone-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-6 text-sm text-stone-500 sm:px-6 lg:px-8">
            Kopi spesialti Nusantara, disangrai dengan penuh perhatian.
        </div>
    </footer>
</body>
</html>
