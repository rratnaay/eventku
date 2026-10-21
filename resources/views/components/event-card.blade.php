@props(['event'])
@php [$statusLabel, $statusColor] = $event->display_status; @endphp
<article class="reveal group card flex flex-col overflow-hidden transition duration-300 hover:-translate-y-1.5 hover:shadow-lift">
    <a href="{{ route('events.show', $event) }}" class="relative block aspect-[4/3] overflow-hidden bg-beige-100" aria-label="Lihat {{ $event->title }}">
        <img src="{{ $event->poster_url }}" alt="Poster {{ $event->title }}" loading="lazy"
             class="size-full object-cover transition duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-t from-cocoa-900/50 via-transparent to-transparent"></div>
        <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-cocoa-700 backdrop-blur">
            <x-icon :name="$event->category->icon" class="size-3.5 text-ember-600" /> {{ $event->category->name }}
        </span>
        <x-badge :color="$statusColor" class="absolute right-3 top-3 shadow">{{ $statusLabel }}</x-badge>
        <span class="absolute bottom-3 left-3 rounded-full bg-cocoa-900/80 px-3 py-1 text-xs font-bold text-cream-50 backdrop-blur">{{ $event->price_label }}</span>
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="flex items-center gap-1.5 text-xs font-semibold text-ember-600">
            <x-icon name="calendar" class="size-4" /> {{ $event->starts_at->locale('id')->translatedFormat('D, d M Y · H:i') }} WIB
        </p>
        <h3 class="mt-2 line-clamp-2 text-xl font-semibold leading-snug">
            <a href="{{ route('events.show', $event) }}" class="hover:text-ember-600">{{ $event->title }}</a>
        </h3>
        <p class="mt-2 flex items-center gap-1.5 text-sm text-cocoa-600">
            <x-icon name="map-pin" class="size-4 shrink-0" /> <span class="line-clamp-1">{{ $event->location }}, {{ $event->city }}</span>
        </p>
        <div class="mt-auto flex items-center justify-between pt-5 text-sm">
            <span class="flex items-center gap-1.5 text-cocoa-600">
                <x-icon name="users" class="size-4" /> {{ $event->seats_left }} / {{ $event->quota }} kursi
            </span>
            <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-1 font-semibold text-cocoa-800 transition hover:gap-2 hover:text-ember-600">
                Detail <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </div>
</article>
