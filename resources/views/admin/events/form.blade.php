@extends('layouts.admin')

@php $isEdit = $event->exists; @endphp
@section('title', $isEdit ? 'Ubah Event' : 'Event Baru')
@section('heading', $isEdit ? 'Ubah Event' : 'Event Baru')
@section('actions')
    <a href="{{ route('admin.events.index') }}" class="btn btn-outline">← Kembali</a>
@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" novalidate
      action="{{ $isEdit ? route('admin.events.update', $event->id) : route('admin.events.store') }}" class="grid gap-8 lg:grid-cols-3">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="card space-y-5 p-6 lg:col-span-2">
        <div>
            <label for="title" class="label">Judul event</label>
            <input id="title" name="title" value="{{ old('title', $event->title) }}" class="input" required>
            <x-error name="title" />
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="category_id" class="label">Kategori</label>
                <select id="category_id" name="category_id" class="input" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $event->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
                <x-error name="category_id" />
            </div>
            <div>
                <label for="organizer" class="label">Penyelenggara</label>
                <input id="organizer" name="organizer" value="{{ old('organizer', $event->organizer) }}" class="input" required>
                <x-error name="organizer" />
            </div>
        </div>
        <div>
            <label for="description" class="label">Deskripsi</label>
            <textarea id="description" name="description" rows="8" class="input" required>{{ old('description', $event->description) }}</textarea>
            <x-error name="description" />
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="location" class="label">Lokasi / venue</label>
                <input id="location" name="location" value="{{ old('location', $event->location) }}" class="input" required>
                <x-error name="location" />
            </div>
            <div>
                <label for="city" class="label">Kota</label>
                <input id="city" name="city" value="{{ old('city', $event->city) }}" class="input" required>
                <x-error name="city" />
            </div>
            <div>
                <label for="starts_at" class="label">Waktu mulai</label>
                <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" class="input" required>
                <x-error name="starts_at" />
            </div>
            <div>
                <label for="ends_at" class="label">Waktu selesai</label>
                <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}" class="input" required>
                <x-error name="ends_at" />
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card space-y-5 p-6">
            <div>
                <label for="quota" class="label">Kuota</label>
                <input id="quota" type="number" min="1" name="quota" value="{{ old('quota', $event->quota) }}" class="input" required>
                @if ($isEdit)<p class="mt-1 text-xs text-cocoa-500">Pendaftar aktif saat ini: {{ $event->active_registrations }}</p>@endif
                <x-error name="quota" />
            </div>
            <div>
                <label for="price" class="label">Harga (Rp) <span class="font-normal text-cocoa-400">— 0 = gratis</span></label>
                <input id="price" type="number" min="0" step="1000" name="price" value="{{ old('price', $event->price) }}" class="input" required>
                <x-error name="price" />
            </div>
            <div>
                <label for="status" class="label">Status</label>
                <select id="status" name="status" class="input" required>
                    @foreach (\App\Models\Event::STATUSES as $key => $label)<option value="{{ $key }}" @selected(old('status', $event->status) === $key)>{{ $label }}</option>@endforeach
                </select>
                <x-error name="status" />
            </div>
            <label class="flex items-center gap-2 text-sm font-semibold text-cocoa-700">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $event->is_featured)) class="size-4 rounded border-beige-300 text-ember-500 focus:ring-ember-500">
                Tampilkan sebagai event unggulan
            </label>
        </div>

        <div class="card p-6">
            <label for="poster-input" class="label">Poster</label>
            <img id="poster-preview" src="{{ $event->exists ? $event->poster_url : asset('images/hero.jpg') }}" alt="Pratinjau poster" class="mb-3 aspect-[4/3] w-full rounded-2xl object-cover">
            <input id="poster-input" type="file" name="poster" accept="image/png,image/jpeg,image/webp" class="input !p-2 text-xs file:mr-3 file:rounded-full file:border-0 file:bg-beige-100 file:px-3 file:py-1.5 file:font-semibold">
            <p class="mt-1 text-xs text-cocoa-500">JPG, PNG, atau WebP. Maks. 2 MB.</p>
            <x-error name="poster" />
        </div>

        <button type="submit" data-once class="btn btn-primary w-full">{{ $isEdit ? 'Simpan Perubahan' : 'Buat Event' }}</button>
    </div>
</form>
@endsection
