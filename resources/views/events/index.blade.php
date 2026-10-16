@extends('layouts.app')

@section('title', 'Jelajah Event — EventKu')
@section('description', 'Temukan event berdasarkan kategori, lokasi, tanggal, dan status pendaftaran.')

@section('content')
<section class="border-b border-beige-200/70 bg-gradient-to-b from-beige-100/60 to-transparent">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <p class="eyebrow">Jelajah</p>
        <h1 class="section-title mt-2">Temukan event untukmu</h1>
        <p class="mt-3 max-w-2xl text-cocoa-600">Gunakan pencarian dan filter untuk menemukan event yang cocok dengan minat, kota, dan jadwalmu.</p>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <form method="GET" action="{{ route('events.index') }}" class="card p-5" role="search" aria-label="Filter event">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <div class="sm:col-span-2 lg:col-span-2">
                <label for="q" class="label">Pencarian</label>
                <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Judul, lokasi, penyelenggara" class="input">
            </div>
            <div>
                <label for="category" class="label">Kategori</label>
                <select id="category" name="category" class="input">
                    <option value="">Semua</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="city" class="label">Lokasi</label>
                <select id="city" name="city" class="input">
                    <option value="">Semua kota</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date" class="label">Tanggal</label>
                <input id="date" type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="input">
            </div>
            <div>
                <label for="status" class="label">Status</label>
                <select id="status" name="status" class="input">
                    <option value="">Semua</option>
                    <option value="open" @selected(($filters['status'] ?? '') === 'open')>Pendaftaran Terbuka</option>
                    <option value="full" @selected(($filters['status'] ?? '') === 'full')>Kuota Penuh</option>
                    <option value="finished" @selected(($filters['status'] ?? '') === 'finished')>Sudah Selesai</option>
                </select>
            </div>
        </div>
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm">
                <label for="sort" class="font-semibold text-cocoa-700">Urutkan</label>
                <select id="sort" name="sort" class="input !w-auto !py-1.5">
                    <option value="soonest" @selected(($filters['sort'] ?? 'soonest') === 'soonest')>Terdekat</option>
                    <option value="latest" @selected(($filters['sort'] ?? '') === 'latest')>Terbaru ditambahkan</option>
                    <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>Harga terendah</option>
                </select>
            </div>
            <div class="flex gap-2">
                @if (collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('events.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="filter" class="size-4" /> Terapkan</button>
            </div>
        </div>
    </form>

    <p class="mt-8 text-sm text-cocoa-600" aria-live="polite">Menampilkan <strong class="text-cocoa-900">{{ $events->total() }}</strong> event</p>

    @if ($events->isEmpty())
        <div class="card mt-6 px-6 py-16 text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-beige-100 text-cocoa-500"><x-icon name="search" class="size-8" /></span>
            <h2 class="mt-4 text-2xl font-semibold">Tidak ada event yang cocok</h2>
            <p class="mt-2 text-cocoa-600">Coba ubah kata kunci atau hapus beberapa filter.</p>
            <a href="{{ route('events.index') }}" class="btn btn-primary mt-6">Reset filter</a>
        </div>
    @else
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)<x-event-card :event="$event" />@endforeach
        </div>
        <div class="mt-10">{{ $events->links() }}</div>
    @endif
</div>
@endsection
