@extends('layouts.app')

@section('title', 'Pendaftaran Saya — EventKu')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Akun saya</p>
            <h1 class="section-title mt-2">Pendaftaran saya</h1>
            <p class="mt-2 text-sm text-cocoa-600">Pantau status verifikasi pembayaran, kode tiket, dan jadwal event yang Anda ikuti.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn btn-outline btn-sm"><x-icon name="search" class="size-4" /> Jelajahi Event Lainnya</a>
    </div>

    @php
        $tabs = [
            [null, 'Semua', $counts['all']],
            ['confirmed', 'Berhasil / Terkonfirmasi', $counts['confirmed']],
            ['pending', 'Menunggu Pembayaran / Verifikasi', $counts['pending']],
            ['cancelled', 'Dibatalkan', $counts['cancelled']],
        ];
    @endphp

    @if ($counts['all'] > 0)
        <nav class="mt-8 flex flex-wrap gap-2" aria-label="Filter status pendaftaran">
            @foreach ($tabs as [$filterKey, $label, $count])
                @php $isActive = $activeFilter === $filterKey; @endphp
                <a href="{{ $filterKey ? route('registrations.index', ['status' => $filterKey]) : route('registrations.index') }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold transition',
                       'border-ember-500 bg-ember-500 text-white shadow-soft' => $isActive,
                       'border-beige-300 bg-white text-cocoa-700 hover:border-ember-400 hover:bg-beige-100/60' => ! $isActive,
                   ])
                   @if ($isActive) aria-current="page" @endif>
                    <span>{{ $label }}</span>
                    <span @class([
                        'rounded-full px-2 py-0.5 text-[11px] font-bold',
                        'bg-white/20 text-white' => $isActive,
                        'bg-beige-100 text-cocoa-700' => ! $isActive,
                    ])>{{ $count }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if ($counts['all'] === 0)
        <div class="card mt-8 px-6 py-16 text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-beige-100 text-ember-600"><x-icon name="ticket" class="size-8" /></span>
            <h2 class="mt-4 text-2xl font-semibold">Belum ada pendaftaran</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-cocoa-600">
                Anda belum memiliki pendaftaran event. Temukan berbagai konser, workshop, olahraga, hingga festival menarik dan daftarkan dirimu sekarang!
            </p>
            <a href="{{ route('events.index') }}" class="btn btn-primary mt-6">Jelajah Event</a>
        </div>
    @elseif ($registrations->isEmpty())
        <div class="card mt-8 px-6 py-14 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-beige-100 text-cocoa-500"><x-icon name="filter" class="size-6" /></span>
            <h2 class="mt-4 text-xl font-semibold">Tidak ada pendaftaran pada kategori status ini</h2>
            <p class="mt-1 text-sm text-cocoa-600">Coba pilih tab filter status lainnya untuk melihat daftar pendaftaran Anda.</p>
            <a href="{{ route('registrations.index') }}" class="btn btn-outline btn-sm mt-5">Lihat Semua Pendaftaran</a>
        </div>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($registrations as $registration)
                @php $event = $registration->event; @endphp
                <li class="card flex flex-col gap-5 p-5 transition hover:shadow-lift sm:flex-row sm:items-center">
                    <a href="{{ route('registrations.show', $registration) }}" class="block shrink-0 overflow-hidden rounded-2xl bg-beige-100 sm:w-36">
                        <img src="{{ $event->poster_url }}" alt="Poster {{ $event->title }}" class="aspect-[4/5] w-full object-cover" loading="lazy">
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge :color="$registration->status_color">{{ $registration->status_label }}</x-badge>
                            <span class="rounded-full bg-beige-100 px-2.5 py-0.5 font-mono text-xs font-semibold text-cocoa-700">{{ $registration->code }}</span>
                            @if ($event->category)
                                <span class="text-xs font-medium text-cocoa-500">· {{ $event->category->name }}</span>
                            @endif
                        </div>

                        <h2 class="mt-2 line-clamp-1 text-xl font-semibold">
                            <a href="{{ route('registrations.show', $registration) }}" class="hover:text-ember-600">{{ $event->title }}</a>
                        </h2>

                        <div class="mt-2 space-y-1 text-sm text-cocoa-600">
                            <p class="flex items-center gap-1.5">
                                <x-icon name="calendar" class="size-4 shrink-0 text-ember-600" />
                                <span>{{ $event->schedule_label }}</span>
                            </p>
                            <p class="flex items-center gap-1.5">
                                <x-icon name="map-pin" class="size-4 shrink-0 text-ember-600" />
                                <span>{{ $event->location }}, {{ $event->city }}</span>
                            </p>
                        </div>

                        @if ($registration->isPending() && ! $event->is_free && ! $registration->payment_proof)
                            <p class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700">
                                <x-icon name="clock" class="size-4" /> Silakan unggah bukti pembayaran ({{ $event->price_label }}) di halaman Detail.
                            </p>
                        @endif
                    </div>

                    <div class="flex shrink-0 gap-2 sm:flex-col sm:items-stretch">
                        <a href="{{ route('registrations.show', $registration) }}" class="btn btn-dark btn-sm">Detail</a>
                        @if (! $registration->isCancelled() && $registration->status !== 'attended' && $event->ends_at->isFuture())
                            <form method="POST" action="{{ route('registrations.cancel', $registration) }}" data-confirm="Batalkan pendaftaran untuk {{ $event->title }}?">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-outline btn-sm w-full">Batalkan</button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-8">{{ $registrations->links() }}</div>
    @endif
</section>
@endsection
