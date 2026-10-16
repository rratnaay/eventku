<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EventKu — Temukan & Ikuti Event Favoritmu')</title>
    <meta name="description" content="@yield('description', 'EventKu adalah platform untuk menemukan event, mendaftar dengan mudah, dan membantu panitia mengelola acara.')">
    <meta name="theme-color" content="#fdfaf4">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-full focus:bg-cocoa-900 focus:px-4 focus:py-2 focus:text-white">Lewati ke konten</a>

    @php
        $nav = [
            ['Beranda', 'home', 'home'],
            ['Jelajah Event', 'events.index', 'events.*'],
            ['Tentang', 'about', 'about'],
            ['Kontak', 'contact', 'contact'],
        ];
    @endphp

    <header class="sticky top-0 z-40 border-b border-beige-200/70 bg-cream-50/85 backdrop-blur-lg">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" aria-label="EventKu beranda"><x-logo /></a>

            <ul class="hidden items-center gap-1 md:flex">
                @foreach ($nav as [$label, $route, $pattern])
                    <li>
                        <a href="{{ route($route) }}"
                           @class(['rounded-full px-4 py-2 text-sm font-semibold transition', 'bg-beige-100 text-cocoa-900' => request()->routeIs($pattern), 'text-cocoa-600 hover:bg-beige-100/70 hover:text-cocoa-900' => ! request()->routeIs($pattern)])
                           @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm"><x-icon name="chart" class="size-4" /> Dashboard</a>
                    @else
                        <a href="{{ route('registrations.index') }}" class="btn btn-outline btn-sm"><x-icon name="ticket" class="size-4" /> Pendaftaran Saya</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-dark btn-sm" type="submit" title="Keluar sebagai {{ auth()->user()->name }}"><x-icon name="logout" class="size-4" /> Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm text-cocoa-700 hover:bg-beige-100">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                @endauth
            </div>

            <button type="button" class="grid size-10 place-items-center rounded-full border border-beige-300 bg-white md:hidden"
                    data-toggle="mobile-menu" aria-expanded="false" aria-controls="mobile-menu" aria-label="Buka menu">
                <x-icon name="menu" />
            </button>
        </nav>

        <div id="mobile-menu" class="hidden border-t border-beige-200 bg-cream-50 px-4 pb-5 pt-3 md:hidden">
            <ul class="space-y-1">
                @foreach ($nav as [$label, $route, $pattern])
                    <li><a href="{{ route($route) }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-beige-100 text-cocoa-900' : 'text-cocoa-600' }}">{{ $label }}</a></li>
                @endforeach
            </ul>
            <div class="mt-4 flex flex-col gap-2">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('registrations.index') }}" class="btn btn-outline">{{ auth()->user()->isAdmin() ? 'Dashboard Admin' : 'Pendaftaran Saya' }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-dark w-full" type="submit">Keluar</button></form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <x-flash />

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-24 bg-cocoa-900 text-cocoa-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-4 lg:px-8">
            <div class="md:col-span-2">
                <x-logo :light="true" />
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-cocoa-100/80">Temukan event yang kamu suka, daftar dalam hitungan detik, dan nikmati pengalaman yang berkesan. Untuk peserta dan panitia.</p>
            </div>
            <div>
                <h2 class="font-sans text-sm font-bold uppercase tracking-widest text-cream-50">Jelajah</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a class="hover:text-ember-400" href="{{ route('events.index') }}">Semua Event</a></li>
                    <li><a class="hover:text-ember-400" href="{{ route('about') }}">Tentang Kami</a></li>
                    <li><a class="hover:text-ember-400" href="{{ route('contact') }}">Hubungi Kami</a></li>
                </ul>
            </div>
            <div>
                <h2 class="font-sans text-sm font-bold uppercase tracking-widest text-cream-50">Akun</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    @auth
                        <li><a class="hover:text-ember-400" href="{{ route('registrations.index') }}">Pendaftaran Saya</a></li>
                    @else
                        <li><a class="hover:text-ember-400" href="{{ route('login') }}">Masuk</a></li>
                        <li><a class="hover:text-ember-400" href="{{ route('register') }}">Buat Akun</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 py-5 text-center text-xs text-cocoa-100/60">© {{ date('Y') }} EventKu. Dibuat dengan Laravel.</div>
    </footer>
</body>
</html>
