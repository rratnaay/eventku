@extends('layouts.app')

@section('title', 'Detail Pendaftaran '.$registration->code.' — EventKu')

@section('content')
@php $event = $registration->event; @endphp
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('registrations.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-cocoa-600 hover:text-ember-600">← Kembali ke pendaftaran saya</a>

    <div class="card mt-5 overflow-hidden">
        <img src="{{ $event->poster_url }}" alt="Poster {{ $event->title }}" class="aspect-[16/7] w-full object-cover">

        <div class="p-7 sm:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-badge :color="$registration->status_color" class="!px-4 !py-1.5 !text-sm">{{ $registration->status_label }}</x-badge>
                <span class="text-xs text-cocoa-500">Terdaftar {{ $registration->registered_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>

            <h1 class="mt-4 text-3xl font-semibold">{{ $event->title }}</h1>

            <div class="mt-8 rounded-3xl border-2 border-dashed border-beige-300 bg-cream-50 p-6 text-center">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-cocoa-500">Kode registrasi</p>
                <p class="mt-2 font-mono text-3xl font-bold tracking-widest text-cocoa-900">{{ $registration->code }}</p>
                <p class="mt-2 text-xs text-cocoa-500">Tunjukkan kode ini saat registrasi ulang di lokasi.</p>
            </div>

            <dl class="mt-8 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Peserta</dt><dd class="mt-1 font-semibold">{{ $registration->user->name }}<br><span class="text-sm font-normal text-cocoa-600">{{ $registration->user->email }}</span></dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Jadwal</dt><dd class="mt-1 font-semibold">{{ $event->schedule_label }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Lokasi</dt><dd class="mt-1 font-semibold">{{ $event->location }}, {{ $event->city }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Harga</dt><dd class="mt-1 font-semibold">{{ $event->price_label }}</dd></div>
            </dl>

            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('events.show', $event) }}" class="btn btn-dark">Lihat Event</a>
                @can('cancel', $registration)
                    @if (! $registration->isCancelled() && $registration->status !== 'attended' && $event->ends_at->isFuture())
                        <form method="POST" action="{{ route('registrations.cancel', $registration) }}" data-confirm="Yakin ingin membatalkan pendaftaran ini?">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline">Batalkan Pendaftaran</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</section>
@endsection
