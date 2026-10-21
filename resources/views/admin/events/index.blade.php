@extends('layouts.admin')

@section('title', 'Kelola Event')
@section('heading', 'Kelola Event')
@section('subheading', 'Buat, ubah, dan hapus event')

@section('actions')
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Event Baru</a>
@endsection

@section('content')
<form method="GET" class="card mb-6 grid gap-4 p-5 sm:grid-cols-4" role="search">
    <div class="sm:col-span-2">
        <label for="q" class="label">Cari</label>
        <input id="q" name="q" value="{{ request('q') }}" placeholder="Judul atau kota" class="input">
    </div>
    <div>
        <label for="category" class="label">Kategori</label>
        <select id="category" name="category" class="input">
            <option value="">Semua</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label for="status" class="label">Status</label>
        <select id="status" name="status" class="input">
            <option value="">Semua</option>
            @foreach (\App\Models\Event::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div class="flex gap-2 sm:col-span-4">
        <button class="btn btn-dark btn-sm" type="submit">Terapkan</button>
        @if (request()->hasAny(['q', 'category', 'status']))<a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
    </div>
</form>

<div class="table-wrap">
    <table class="table-x">
        <thead><tr><th>Event</th><th>Jadwal</th><th>Kuota</th><th>Harga</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
        <tbody>
            @forelse ($events as $event)
                @php [$sl, $sc] = $event->display_status; @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $event->poster_url }}" alt="" class="size-12 rounded-xl object-cover">
                            <div class="min-w-0">
                                <a href="{{ route('events.show', $event) }}" class="line-clamp-1 font-semibold hover:text-ember-600">{{ $event->title }}</a>
                                <p class="text-xs text-cocoa-500">{{ $event->category->name }} · {{ $event->city }}@if ($event->is_featured) · <span class="font-semibold text-ember-600">Unggulan</span>@endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap text-xs">{{ $event->starts_at->format('d M Y H:i') }}</td>
                    <td>{{ $event->active_registrations_count }}/{{ $event->quota }}</td>
                    <td class="whitespace-nowrap">{{ $event->price_label }}</td>
                    <td><x-badge :color="$sc">{{ $sl }}</x-badge></td>
                    <td>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.registrations.index', ['event' => $event->id]) }}" class="btn btn-outline btn-sm" title="Pendaftar"><x-icon name="users" class="size-4" /></a>
                            <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-outline btn-sm" title="Ubah"><x-icon name="pencil" class="size-4" /></a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}" data-confirm="Hapus event &quot;{{ $event->title }}&quot;? Semua pendaftarannya juga akan terhapus.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><x-icon name="trash" class="size-4" /></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-12 text-center text-cocoa-500">Tidak ada event ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $events->links() }}</div>
@endsection
