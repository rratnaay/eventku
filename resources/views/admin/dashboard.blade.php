@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Ringkasan aktivitas EventKu')

@section('actions')
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Event Baru</a>
@endsection

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ([
        ['Total Event', $stats['events'], 'calendar'],
        ['Pengguna', $stats['users'], 'users'],
        ['Pendaftaran Aktif', $stats['registrations'], 'ticket'],
        ['Event Aktif', $stats['active'], 'sparkles'],
        ['Event Selesai', $stats['finished'], 'check'],
    ] as [$label, $value, $icon])
        <div class="card p-5">
            <span class="grid size-10 place-items-center rounded-xl bg-beige-100 text-ember-600"><x-icon :name="$icon" class="size-5" /></span>
            <p class="mt-4 font-display text-3xl font-semibold text-cocoa-900">{{ number_format($value) }}</p>
            <p class="text-xs font-semibold uppercase tracking-wider text-cocoa-500">{{ $label }}</p>
        </div>
    @endforeach
</div>

<section class="card mt-8 p-6" aria-labelledby="chart-title">
    <div class="flex items-center justify-between">
        <h2 id="chart-title" class="text-xl font-semibold">Pendaftaran 14 hari terakhir</h2>
        <span class="text-sm text-cocoa-500">Total: <strong class="text-cocoa-900">{{ $chart->sum('total') }}</strong></span>
    </div>
    @php $max = max(1, $chart->max('total')); @endphp
    <div class="mt-6 flex h-52 items-end gap-2" role="img" aria-label="Grafik batang jumlah pendaftaran per hari">
        @foreach ($chart as $bar)
            <div class="group flex h-full flex-1 flex-col items-center justify-end gap-2">
                <span class="text-xs font-semibold text-cocoa-700 opacity-0 transition group-hover:opacity-100">{{ $bar['total'] }}</span>
                <div class="w-full rounded-t-xl bg-gradient-to-t from-ember-600 to-ember-400 transition-all duration-500 group-hover:from-cocoa-700 group-hover:to-cocoa-500"
                     style="height: {{ max(4, ($bar['total'] / $max) * 100) }}%" title="{{ $bar['label'] }}: {{ $bar['total'] }} pendaftaran"></div>
                <span class="text-[10px] text-cocoa-500">{{ $bar['label'] }}</span>
            </div>
        @endforeach
    </div>
</section>

<div class="mt-8 grid gap-8 xl:grid-cols-2">
    <section aria-labelledby="latest-events">
        <div class="mb-3 flex items-center justify-between"><h2 id="latest-events" class="text-xl font-semibold">Event terbaru</h2><a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-ember-600 hover:underline">Semua</a></div>
        <div class="table-wrap">
            <table class="table-x">
                <thead><tr><th>Event</th><th>Kuota</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($latestEvents as $event)
                        @php [$sl, $sc] = $event->display_status; @endphp
                        <tr>
                            <td><a href="{{ route('admin.events.edit', $event->id) }}" class="font-semibold hover:text-ember-600">{{ $event->title }}</a><p class="text-xs text-cocoa-500">{{ $event->category->name }}</p></td>
                            <td>{{ $event->active_registrations_count }}/{{ $event->quota }}</td>
                            <td><x-badge :color="$sc">{{ $sl }}</x-badge></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-cocoa-500">Belum ada event.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section aria-labelledby="latest-regs">
        <div class="mb-3 flex items-center justify-between"><h2 id="latest-regs" class="text-xl font-semibold">Pendaftaran terbaru</h2><a href="{{ route('admin.registrations.index') }}" class="text-sm font-semibold text-ember-600 hover:underline">Semua</a></div>
        <div class="table-wrap">
            <table class="table-x">
                <thead><tr><th>Peserta</th><th>Event</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($latestRegistrations as $r)
                        <tr>
                            <td><p class="font-semibold">{{ $r->user->name }}</p><p class="text-xs text-cocoa-500">{{ $r->registered_at->diffForHumans() }}</p></td>
                            <td class="max-w-[12rem] truncate">{{ $r->event->title }}</td>
                            <td><x-badge :color="$r->status_color">{{ $r->status_label }}</x-badge></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-cocoa-500">Belum ada pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
