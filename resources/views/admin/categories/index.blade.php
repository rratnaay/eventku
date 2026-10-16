@extends('layouts.admin')

@section('title', 'Kategori')
@section('heading', 'Kategori')
@section('subheading', 'Kelompokkan event berdasarkan minat')

@section('content')
@php
    $icons = \App\Http\Requests\Admin\CategoryRequest::ICONS;
    $editing = $editing ?? null;
@endphp
<div class="grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="table-wrap">
            <table class="table-x">
                <thead><tr><th>Kategori</th><th>Event</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="grid size-10 place-items-center rounded-xl bg-beige-100 text-cocoa-700"><x-icon :name="$category->icon" class="size-5" /></span>
                                    <div><p class="font-semibold">{{ $category->name }}</p><p class="text-xs text-cocoa-500">{{ $category->slug }}</p></div>
                                </div>
                            </td>
                            <td>{{ $category->events_count }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline btn-sm" title="Ubah"><x-icon name="pencil" class="size-4" /></a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Hapus kategori &quot;{{ $category->name }}&quot;?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><x-icon name="trash" class="size-4" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-10 text-center text-cocoa-500">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $categories->links() }}</div>
    </div>

    <form method="POST" novalidate class="card h-fit space-y-5 p-6"
          action="{{ $editing ? route('admin.categories.update', $editing) : route('admin.categories.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <h2 class="text-xl font-semibold">{{ $editing ? 'Ubah kategori' : 'Tambah kategori' }}</h2>
        <div>
            <label for="name" class="label">Nama</label>
            <input id="name" name="name" value="{{ old('name', $editing?->name) }}" class="input" required>
            <x-error name="name" />
        </div>
        <div>
            <label for="icon" class="label">Ikon</label>
            <select id="icon" name="icon" class="input">
                @foreach ($icons as $icon)<option value="{{ $icon }}" @selected(old('icon', $editing?->icon ?? 'sparkles') === $icon)>{{ ucfirst($icon) }}</option>@endforeach
            </select>
            <x-error name="icon" />
        </div>
        <div>
            <label for="description" class="label">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="input">{{ old('description', $editing?->description) }}</textarea>
            <x-error name="description" />
        </div>
        <div class="flex gap-2">
            <button type="submit" data-once class="btn btn-primary flex-1">{{ $editing ? 'Simpan' : 'Tambah' }}</button>
            @if ($editing)<a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Batal</a>@endif
        </div>
    </form>
</div>
@endsection
