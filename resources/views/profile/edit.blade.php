@extends('layouts.app')

@section('title', 'Profil Saya — EventKu')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Akun saya</p>
            <h1 class="section-title mt-2">Profil pengguna</h1>
            <p class="mt-2 text-sm text-cocoa-600">Data profil ini digunakan secara otomatis saat Anda mendaftar event di EventKu.</p>
        </div>
        @if (! $user->isAdmin())
            <a href="{{ route('registrations.index') }}" class="btn btn-outline btn-sm"><x-icon name="ticket" class="size-4" /> Lihat Pendaftaran Saya</a>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        {{-- Ringkasan Informasi Akun --}}
        <aside class="space-y-6 lg:col-span-1">
            <div class="card p-6">
                <div class="flex items-center gap-4">
                    <span class="grid size-16 shrink-0 place-items-center rounded-3xl bg-ember-500 font-display text-2xl font-bold text-white shadow-soft">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <h2 class="truncate text-xl font-semibold text-cocoa-900">{{ $user->name }}</h2>
                        <p class="truncate text-sm text-cocoa-600">{{ $user->email }}</p>
                        <x-badge color="green" class="mt-2">{{ $user->isAdmin() ? 'Administrator' : 'Peserta Aktif' }}</x-badge>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 border-t border-beige-200/80 pt-5 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-cocoa-500">Nomor WhatsApp</dt>
                        <dd class="font-semibold text-cocoa-900">{{ $user->phone ?: 'Belum diisi' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-cocoa-500">Instansi / Komunitas</dt>
                        <dd class="font-semibold text-cocoa-900">{{ $user->institution ?: 'Belum diisi' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-cocoa-500">Kota / Domisili</dt>
                        <dd class="font-semibold text-cocoa-900">{{ $user->city ?: 'Belum diisi' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-cocoa-500">Bergabung sejak</dt>
                        <dd class="font-semibold text-cocoa-900">{{ $user->created_at?->locale('id')->translatedFormat('d M Y') ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="card-beige p-6">
                <h3 class="text-base font-semibold text-cocoa-900">Ringkasan Pendaftaran</h3>
                <dl class="mt-4 grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-2xl bg-white p-3 shadow-soft">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-cocoa-500">Terkonfirmasi</dt>
                        <dd class="mt-1 font-display text-2xl font-semibold text-emerald-700">{{ $stats['confirmed'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-white p-3 shadow-soft">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-cocoa-500">Menunggu</dt>
                        <dd class="mt-1 font-display text-2xl font-semibold text-amber-700">{{ $stats['pending'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-white p-3 shadow-soft">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-cocoa-500">Dibatalkan</dt>
                        <dd class="mt-1 font-display text-2xl font-semibold text-red-700">{{ $stats['cancelled'] }}</dd>
                    </div>
                    <div class="rounded-2xl bg-white p-3 shadow-soft">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-cocoa-500">Total</dt>
                        <dd class="mt-1 font-display text-2xl font-semibold text-cocoa-900">{{ $stats['total'] }}</dd>
                    </div>
                </dl>
            </div>
        </aside>

        {{-- Form Ubah Profil --}}
        <div class="card p-7 sm:p-10 lg:col-span-2">
            <h2 class="text-2xl font-semibold">Perbarui Informasi Profil</h2>
            <p class="mt-1 text-sm text-cocoa-600">Lengkapi data di bawah ini agar formulir pendaftaran event terisi otomatis.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="mt-7 space-y-5" novalidate>
                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="label">Nama lengkap</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required autocomplete="name">
                        <x-error name="name" />
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required autocomplete="email">
                        <x-error name="email" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="label">Nomor WhatsApp</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" class="input" autocomplete="tel" inputmode="tel">
                        <p class="mt-1 text-xs text-cocoa-500">Digunakan untuk informasi tiket & konfirmasi pendaftaran event.</p>
                        <x-error name="phone" />
                    </div>
                    <div>
                        <label for="city" class="label">Kota / Domisili</label>
                        <input id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="Contoh: Jakarta, Bandung, Surabaya" class="input">
                        <x-error name="city" />
                    </div>
                </div>

                <div>
                    <label for="institution" class="label">Instansi / Kampus / Komunitas</label>
                    <input id="institution" name="institution" value="{{ old('institution', $user->institution) }}" placeholder="Contoh: Universitas Indonesia / Komunitas Desain" class="input">
                    <x-error name="institution" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-beige-200/80 pt-5">
                    <p class="text-xs text-cocoa-500">Perubahan profil langsung tersimpan pada akun Anda.</p>
                    <button type="submit" data-once class="btn btn-primary">
                        <x-icon name="check" class="size-4" /> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
