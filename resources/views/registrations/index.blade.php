@extends('layouts.app')

@section('title', 'Pendaftaran Saya — EventKu')

@section('content')
<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
    <p class="eyebrow">Akun saya</p>
    <h1 class="section-title mt-2">Pendaftaran saya</h1>

    @if ($registrations->isEmpty())
        <div class="card mt-8 px-6 py-16 text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-beige-100 text-cocoa-500"><x-icon name="ticket" class="size-8" /></span>
            <h2 class="mt-4 text-2xl font-semibold">Belum ada pendaftaran</h2>
            <p class="mt-2 text-cocoa-600">Temukan event menarik dan daftarkan dirimu.</p>
            <a href="{{ route('events.index') }}" class="btn btn-primary mt-6">Jelajah Event</a>
        </div>
    @else
        <ul class="mt-8 space-y-4">
            @foreach ($registrations as $registration)
                @php $event = $registration->event; @endphp
                <li class="card flex flex-col gap-5 p-4 transition hover:shadow-lift sm:flex-row sm:items-center">
                    <img src="{{ $event->poster_url }}" alt="" class="aspect-[4/3] w-full rounded-2xl object-cover sm:w-40" loading="lazy">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge :color="$registration->status_color">{{ $registration->status_label }}</x-badge>
                            <span class="font-mono text-xs font-semibold text-cocoa-500">{{ $registration->code }}</span>
                        </div>
                        <h2 class="mt-2 line-clamp-1 text-xl font-semibold">{{ $event->title }}</h2>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-cocoa-600"><x-icon name="calendar" class="size-4" /> {{ $event->starts_at->locale('id')->translatedFormat('l, d F Y · H:i') }} WIB</p>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-cocoa-600"><x-icon name="map-pin" class="size-4" /> {{ $event->location }}, {{ $event->city }}</p>
                    </div>
                    <div class="flex gap-2 sm:flex-col">
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
