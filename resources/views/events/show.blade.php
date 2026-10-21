@extends('layouts.app')

@section('title', $event->title.' — EventKu')
@section('description', \Illuminate\Support\Str::limit(strip_tags($event->description), 150))

@section('content')
@php [$statusLabel, $statusColor] = $event->display_status; @endphp
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <nav class="mb-6 text-sm text-cocoa-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-ember-600">Beranda</a> /
        <a href="{{ route('events.index') }}" class="hover:text-ember-600">Event</a> /
        <span class="text-cocoa-800">{{ $event->title }}</span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-3">
        <article class="lg:col-span-2">
            <div class="overflow-hidden rounded-[2rem] border-8 border-white shadow-lift">
                <img src="{{ $event->poster_url }}" alt="Poster {{ $event->title }}" class="aspect-[16/10] w-full object-cover">
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-2">
                <x-badge color="gray" class="!bg-beige-100 !text-cocoa-700"><x-icon :name="$event->category->icon" class="size-3.5" /> {{ $event->category->name }}</x-badge>
                <x-badge :color="$statusColor">{{ $statusLabel }}</x-badge>
                @if ($event->status === 'draft')<x-badge color="amber">Hanya terlihat oleh admin</x-badge>@endif
            </div>
            <h1 class="mt-4 text-4xl font-semibold leading-tight sm:text-5xl">{{ $event->title }}</h1>
            <p class="mt-2 text-cocoa-600">Diselenggarakan oleh <strong class="text-cocoa-800">{{ $event->organizer }}</strong></p>

            <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="card-beige flex items-start gap-3 p-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white text-ember-600 shadow-soft"><x-icon name="calendar" /></span>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Jadwal</dt><dd class="mt-0.5 text-sm font-semibold text-cocoa-900">{{ $event->schedule_label }}</dd></div>
                </div>
                <div class="card-beige flex items-start gap-3 p-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white text-ember-600 shadow-soft"><x-icon name="map-pin" /></span>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Lokasi</dt><dd class="mt-0.5 text-sm font-semibold text-cocoa-900">{{ $event->location }}, {{ $event->city }}</dd></div>
                </div>
            </dl>

            <section class="mt-10" aria-labelledby="desc-title">
                <h2 id="desc-title" class="text-2xl font-semibold">Tentang event ini</h2>
                <div class="mt-4 space-y-4 leading-relaxed text-cocoa-700">
                    @foreach (preg_split('/\n{2,}/', trim($event->description)) as $paragraph)
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endforeach
                </div>
            </section>
        </article>

        <aside class="lg:col-span-1">
            <div class="card sticky top-24 p-6">
                <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Harga tiket</p>
                <p class="font-display text-4xl font-semibold text-cocoa-900">{{ $event->price_label }}</p>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-1.5 font-semibold text-cocoa-700"><x-icon name="users" class="size-4" /> Kuota</span>
                        <span class="text-cocoa-600"><strong class="text-cocoa-900">{{ $event->active_registrations }}</strong> / {{ $event->quota }} terdaftar</span>
                    </div>
                    @php $pct = min(100, round($event->active_registrations / max(1, $event->quota) * 100)); @endphp
                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-beige-100" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kuota terisi">
                        <div class="h-full rounded-full bg-gradient-to-r from-ember-400 to-ember-600 transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-cocoa-500">{{ $event->seats_left > 0 ? "Tersisa {$event->seats_left} kursi" : 'Kuota sudah penuh' }}</p>
                </div>

                <div class="mt-6 space-y-3">
                    @guest
                        @if ($event->is_open)
                            <a href="{{ route('login') }}" class="btn btn-primary w-full">Masuk untuk Mendaftar</a>
                            <p class="text-center text-xs text-cocoa-500">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-ember-600 hover:underline">Daftar gratis</a></p>
                        @else
                            <button class="btn btn-primary w-full" disabled>{{ $event->closedReason() }}</button>
                        @endif
                    @else
                        @if ($registration && ! $registration->isCancelled())
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                                <p class="flex items-center gap-2 font-semibold"><x-icon name="check" class="size-5" /> Anda sudah terdaftar</p>
                                <p class="mt-1 text-xs">Kode: <strong class="font-mono">{{ $registration->code }}</strong></p>
                            </div>
                            <a href="{{ route('registrations.show', $registration) }}" class="btn btn-dark w-full">Lihat Pendaftaran</a>
                        @elseif ($event->is_open)
                            <form method="POST" action="{{ route('events.register', $event) }}">
                                @csrf
                                <button type="submit" data-once class="btn btn-primary w-full"><x-icon name="ticket" class="size-5" /> Daftar Sekarang</button>
                            </form>
                        @else
                            <button class="btn btn-primary w-full" disabled>{{ $event->closedReason() }}</button>
                        @endif
                    @endguest
                </div>
            </div>
        </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-20" aria-labelledby="related-title">
            <h2 id="related-title" class="section-title">Event serupa</h2>
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)<x-event-card :event="$item" />@endforeach
            </div>
        </section>
    @endif
</div>
@endsection
