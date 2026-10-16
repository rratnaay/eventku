@extends('layouts.admin')

@section('title', 'Pendaftar')
@section('heading', 'Pendaftar')
@section('subheading', 'Pantau dan kelola pendaftaran peserta')

@section('content')
<form method="GET" class="card mb-6 grid gap-4 p-5 sm:grid-cols-4" role="search">
    <div>
        <label for="q" class="label">Cari</label>
        <input id="q" name="q" value="{{ request('q') }}" placeholder="Nama, email, kode" class="input">
    </div>
    <div class="sm:col-span-2">
        <label for="event" class="label">Event</label>
        <select id="event" name="event" class="input">
            <option value="">Semua event</option>
            @foreach ($events as $e)<option value="{{ $e->id }}" @selected(request('event') == $e->id)>{{ $e->title }}</option>@endforeach
        </select>
    </div>
    <div>
        <label for="status" class="label">Status</label>
        <select id="status" name="status" class="input">
            <option value="">Semua</option>
            @foreach (\App\Models\Registration::STATUSES as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div class="flex gap-2 sm:col-span-4">
        <button class="btn btn-dark btn-sm" type="submit">Terapkan</button>
        @if (request()->hasAny(['q', 'event', 'status']))<a href="{{ route('admin.registrations.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
    </div>
</form>

<div class="table-wrap">
    <table class="table-x">
        <thead><tr><th>Kode</th><th>Peserta</th><th>Event</th><th>Terdaftar</th><th>Status</th></tr></thead>
        <tbody>
            @forelse ($registrations as $r)
                <tr>
                    <td class="font-mono text-xs font-semibold">{{ $r->code }}</td>
                    <td><p class="font-semibold">{{ $r->user->name }}</p><p class="text-xs text-cocoa-500">{{ $r->user->email }}</p></td>
                    <td class="max-w-[14rem] truncate">{{ $r->event->title }}</td>
                    <td class="whitespace-nowrap text-xs">{{ $r->registered_at->format('d M Y H:i') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.registrations.update', $r) }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="status-{{ $r->id }}">Status untuk {{ $r->code }}</label>
                            <select id="status-{{ $r->id }}" name="status" class="input !w-auto !py-1.5 !text-xs" onchange="this.form.requestSubmit()">
                                @foreach (\App\Models\Registration::STATUSES as $key => $label)<option value="{{ $key }}" @selected($r->status === $key)>{{ $label }}</option>@endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-12 text-center text-cocoa-500">Tidak ada pendaftaran ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $registrations->links() }}</div>
@endsection
