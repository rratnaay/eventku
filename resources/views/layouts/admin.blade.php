<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — EventKu Admin</title>
    <meta name="robots" content="noindex">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream-100">
    @php
        $menu = [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', 'chart'],
            ['Event', 'admin.events.index', 'admin.events.*', 'calendar'],
            ['Kategori', 'admin.categories.index', 'admin.categories.*', 'tag'],
            ['Pendaftar', 'admin.registrations.index', 'admin.registrations.*', 'users'],
        ];
    @endphp

    <div class="lg:flex">
        <aside class="bg-cocoa-900 text-cocoa-100 lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:shrink-0">
            <div class="flex items-center justify-between px-5 py-4 lg:block lg:py-6">
                <a href="{{ route('admin.dashboard') }}"><x-logo :light="true" /></a>
                <button type="button" class="grid size-10 place-items-center rounded-full border border-white/20 lg:hidden" data-toggle="admin-menu" aria-expanded="false" aria-controls="admin-menu" aria-label="Buka menu admin">
                    <x-icon name="menu" />
                </button>
            </div>
            <nav id="admin-menu" class="hidden px-3 pb-5 lg:block" aria-label="Menu admin">
                <ul class="space-y-1">
                    @foreach ($menu as [$label, $route, $pattern, $icon])
                        <li>
                            <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition', 'bg-ember-500 text-white shadow-soft' => request()->routeIs($pattern), 'hover:bg-white/10' => ! request()->routeIs($pattern)])>
                                <x-icon :name="$icon" class="size-5" /> {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 border-t border-white/10 pt-4 text-sm">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 hover:bg-white/10"><x-icon name="home" class="size-5" /> Lihat Situs</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 hover:bg-white/10"><x-icon name="logout" class="size-5" /> Keluar</button>
                    </form>
                    <p class="mt-3 px-4 text-xs text-cocoa-100/60">Masuk sebagai<br><span class="font-semibold text-cream-50">{{ auth()->user()->name }}</span></p>
                </div>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            <x-flash />
            <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-10">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-semibold">@yield('heading')</h1>
                        @hasSection('subheading')<p class="mt-1 text-sm text-cocoa-600">@yield('subheading')</p>@endif
                    </div>
                    @yield('actions')
                </div>
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
