@extends('layouts.app')

@section('title', 'EventKu — Temukan & Ikuti Event Favoritmu')

@section('content')
{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute -right-32 -top-32 size-[28rem] rounded-full bg-ember-400/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-24 top-40 size-72 rounded-full bg-beige-300/40 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:pb-24 lg:pt-20">
        <div class="animate-fade-up">
            <span class="inline-flex items-center gap-2 rounded-full border border-beige-300 bg-white px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-ember-600 shadow-soft">
                <x-icon name="sparkles" class="size-4" /> Platform Event Indonesia
            </span>
            <h1 class="mt-6 text-5xl font-semibold leading-[1.05] sm:text-6xl">
                Temukan momen <span class="relative whitespace-nowrap text-ember-500">seru<svg class="absolute -bottom-2 left-0 w-full text-ember-400" viewBox="0 0 200 10" fill="none" aria-hidden="true"><path d="M2 7c40-6 100-6 196-1" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg></span> berikutnya.
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-cocoa-600">
                Dari konser, konferensi, hingga workshop komunitas. Jelajahi event terdekat, daftar dalam hitungan detik, dan kelola semuanya dalam satu tempat.
            </p>

            <form action="{{ route('events.index') }}" method="GET" class="mt-8 rounded-[2rem] border border-beige-200 bg-white p-2 shadow-lift sm:flex sm:items-center" role="search">
                <label class="flex flex-1 items-center gap-3 px-4 py-2">
                    <x-icon name="search" class="size-5 text-cocoa-300" />
                    <span class="sr-only">Cari event</span>
                    <input type="search" name="q" placeholder="Cari event, lokasi, atau penyelenggara…" class="w-full bg-transparent text-sm placeholder:text-cocoa-300 focus:outline-none">
                </label>
                <label class="hidden items-center gap-2 border-l border-beige-200 px-4 py-2 sm:flex">
                    <x-icon name="map-pin" class="size-5 text-cocoa-300" />
                    <span class="sr-only">Kota</span>
                    <select name="city" class="bg-transparent text-sm focus:outline-none">
                        <option value="">Semua kota</option>
                        @foreach ($cities as $city)<option value="{{ $city }}">{{ $city }}</option>@endforeach
                    </select>
                </label>
                <button class="btn btn-primary mt-2 w-full sm:mt-0 sm:w-auto" type="submit">Cari Event</button>
            </form>

            <dl class="mt-10 grid max-w-md grid-cols-3 gap-4">
                @foreach ([['Event', $stats['events']], ['Peserta', $stats['users']], ['Pendaftaran', $stats['registrations']]] as [$label, $value])
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-cocoa-500">{{ $label }}</dt>
                        <dd class="font-display text-3xl font-semibold text-cocoa-900">{{ number_format($value) }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="relative animate-fade-up [animation-delay:150ms]">
            <div class="relative overflow-hidden rounded-[2.5rem] border-8 border-white shadow-lift">
                <img src="{{ asset('images/hero.jpg') }}" alt="Pengunjung festival tertawa bersama di bawah lampu gantung saat senja" class="aspect-[4/5] w-full object-cover sm:aspect-[4/3] lg:aspect-[4/5]" fetchpriority="high">
            </div>
            @if ($featured->first())
                <a href="{{ route('events.show', $featured->first()) }}" class="absolute -bottom-6 -left-4 flex max-w-[16rem] animate-float items-center gap-3 rounded-3xl border border-beige-200 bg-white p-3 pr-5 shadow-lift transition hover:-translate-y-1 sm:-left-8">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-ember-500 text-white"><x-icon name="calendar" /></span>
                    <span class="min-w-0">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-ember-600">Segera hadir</span>
                        <span class="line-clamp-1 text-sm font-semibold text-cocoa-900">{{ $featured->first()->title }}</span>
                        <span class="text-xs text-cocoa-500">{{ $featured->first()->starts_at->locale('id')->translatedFormat('d M Y') }}</span>
                    </span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- KATEGORI --}}
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="kategori-title">
    <div class="reveal flex items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Kategori</p>
            <h2 id="kategori-title" class="section-title mt-2">Cari berdasarkan minat</h2>
        </div>
    </div>
    <ul class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($categories as $category)
            <li class="reveal">
                <a href="{{ route('events.index', ['category' => $category->slug]) }}" class="card-beige group flex h-full flex-col items-center gap-3 px-4 py-6 text-center transition duration-300 hover:-translate-y-1 hover:border-ember-400 hover:bg-white hover:shadow-lift">
                    <span class="grid size-14 place-items-center rounded-2xl bg-white text-cocoa-700 shadow-soft transition group-hover:bg-ember-500 group-hover:text-white"><x-icon :name="$category->icon" class="size-7" /></span>
                    <span class="font-semibold text-cocoa-900">{{ $category->name }}</span>
                    <span class="text-xs text-cocoa-500">{{ $category->events_count }} event</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>

{{-- UNGGULAN --}}
@if ($featured->isNotEmpty())
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="unggulan-title">
    <div class="reveal mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Pilihan Editor</p>
            <h2 id="unggulan-title" class="section-title mt-2">Event unggulan</h2>
        </div>
        <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-cocoa-700 hover:text-ember-600">Lihat semua <x-icon name="arrow-right" class="size-4" /></a>
    </div>
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($featured as $event)<x-event-card :event="$event" />@endforeach
    </div>
</section>
@endif

{{-- TERBARU --}}
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="terbaru-title">
    <div class="reveal mb-8">
        <p class="eyebrow">Baru ditambahkan</p>
        <h2 id="terbaru-title" class="section-title mt-2">Event terbaru</h2>
    </div>
    @if ($latest->isEmpty())
        <p class="card p-10 text-center text-cocoa-600">Belum ada event yang tersedia.</p>
    @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latest as $event)<x-event-card :event="$event" />@endforeach
        </div>
    @endif
</section>

{{-- CTA --}}
<section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
    <div class="reveal relative overflow-hidden rounded-[2.5rem] bg-cocoa-800 px-6 py-14 text-center sm:px-14">
        <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-ember-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 size-64 rounded-full bg-cocoa-500/40 blur-3xl"></div>
        <h2 class="relative mx-auto max-w-2xl text-3xl font-semibold text-cream-50 sm:text-4xl">Siap ikut event pertamamu?</h2>
        <p class="relative mx-auto mt-4 max-w-xl text-cocoa-100/80">Buat akun gratis, pilih event favorit, dan dapatkan kode registrasi langsung setelah mendaftar.</p>
        <div class="relative mt-8 flex flex-wrap justify-center gap-3">
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary">Buat Akun Gratis</a>
            @endguest
            <a href="{{ route('events.index') }}" class="btn border border-white/30 text-cream-50 hover:bg-white/10">Jelajahi Event</a>
        </div>
    </div>
</section>
@endsection
