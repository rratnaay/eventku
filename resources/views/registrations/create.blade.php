@extends('layouts.app')

@section('title', 'Formulir Pendaftaran '.$event->title.' — EventKu')

@section('content')
<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 text-sm text-cocoa-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-ember-600">Beranda</a> /
        <a href="{{ route('events.index') }}" class="hover:text-ember-600">Event</a> /
        <a href="{{ route('events.show', $event) }}" class="hover:text-ember-600">{{ $event->title }}</a> /
        <span class="text-cocoa-800">Formulir Pendaftaran</span>
    </nav>

    <div class="mb-8">
        <p class="eyebrow">Pendaftaran Event</p>
        <h1 class="section-title mt-2">Formulir Pendaftaran</h1>
        <p class="mt-2 text-sm text-cocoa-600">Periksa dan lengkapi data peserta serta informasi pembayaran di bawah ini.</p>
    </div>

    <form method="POST" action="{{ route('events.register', $event) }}" enctype="multipart/form-data" class="grid gap-8 lg:grid-cols-3" novalidate>
        @csrf

        <div class="space-y-6 lg:col-span-2">
            {{-- 1. Data Peserta dari Profil --}}
            <div class="card p-7 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xl font-semibold">1. Informasi Peserta</h2>
                        <p class="mt-1 text-xs text-cocoa-500">Terisi otomatis dari <a href="{{ route('profile.edit') }}" class="font-semibold text-ember-600 hover:underline">Profil Anda</a>. Perubahan juga akan disimpan ke profil.</p>
                    </div>
                    <x-badge color="green"><x-icon name="user" class="size-3.5" /> Terhubung dengan Profil</x-badge>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
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
                    <div>
                        <label for="phone" class="label">Nomor WhatsApp</label>
                        <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="081234567890" class="input" required autocomplete="tel" inputmode="tel">
                        <x-error name="phone" />
                    </div>
                    <div>
                        <label for="city" class="label">Kota / Domisili</label>
                        <input id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="Contoh: Jakarta" class="input">
                        <x-error name="city" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="institution" class="label">Instansi / Kampus / Komunitas <span class="font-normal text-cocoa-400">(opsional)</span></label>
                        <input id="institution" name="institution" value="{{ old('institution', $user->institution) }}" placeholder="Contoh: Universitas Brawijaya / Komunitas Laravel" class="input">
                        <x-error name="institution" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Catatan tambahan <span class="font-normal text-cocoa-400">(opsional)</span></label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Kebutuhan khusus atau pertanyaan singkat untuk panitia" class="input">{{ old('notes') }}</textarea>
                        <x-error name="notes" />
                    </div>
                </div>
            </div>

            {{-- 2. Informasi Pembayaran --}}
            <div class="card p-7 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-xl font-semibold">2. Informasi Pembayaran</h2>
                    <span class="font-display text-2xl font-semibold text-ember-600">{{ $event->price_label }}</span>
                </div>

                @if ($event->is_free)
                    <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5 text-sm text-emerald-900">
                        <p class="flex items-center gap-2 font-semibold"><x-icon name="check" class="size-5 text-emerald-600" /> Event Gratis — Tidak Perlu Pembayaran</p>
                        <p class="mt-1 text-xs leading-relaxed text-emerald-800">
                            Event ini tidak memungut biaya tiket. Setelah mengirim formulir pendaftaran, pendaftaran Anda akan tercatat dan diverifikasi oleh panitia.
                        </p>
                    </div>
                @else
                    <p class="mt-2 text-sm text-cocoa-600">
                        Silakan lakukan pembayaran sesuai nominal tiket <strong class="text-cocoa-900">{{ $event->price_label }}</strong> ke salah satu rekening resmi EventKu di bawah ini, lalu unggah bukti pembayaran Anda.
                    </p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div class="card-beige p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Transfer Bank</p>
                            <p class="mt-1 text-sm font-semibold text-cocoa-900">BCA · 8720 1928 37</p>
                            <p class="text-sm font-semibold text-cocoa-900">Mandiri · 1370 0098 1234 5</p>
                            <p class="mt-1 text-xs text-cocoa-600">a.n. PT EventKu Indonesia</p>
                        </div>
                        <div class="card-beige p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">QRIS & E-Wallet</p>
                            <p class="mt-1 text-sm font-semibold text-cocoa-900">GoPay / OVO / DANA · 0812-3456-7890</p>
                            <p class="mt-1 text-xs text-cocoa-600">a.n. EventKu Official</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label for="payment_method" class="label">Pilih Metode Pembayaran</label>
                            <select id="payment_method" name="payment_method" class="input">
                                @foreach (\App\Models\Registration::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}" @selected(old('payment_method', 'transfer_bank') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-error name="payment_method" />
                        </div>

                        <div>
                            <label for="payment_proof" class="label">Unggah Bukti Pembayaran <span class="font-normal text-cocoa-400">(JPG, PNG, WebP · maks. 2 MB)</span></label>
                            <input id="payment_proof" type="file" name="payment_proof" accept="image/png,image/jpeg,image/webp"
                                   class="input !p-2 text-xs file:mr-3 file:rounded-full file:border-0 file:bg-beige-100 file:px-4 file:py-2 file:font-semibold file:text-cocoa-800 hover:file:bg-beige-200">
                            <p class="mt-1.5 text-xs text-cocoa-500">
                                Unggah tangkapan layar atau foto resi transfer. Anda juga dapat mengunggah bukti pembayaran nanti melalui halaman Detail Pendaftaran.
                            </p>
                            <x-error name="payment_proof" />
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Ringkasan Event --}}
        <aside class="lg:col-span-1">
            <div class="card sticky top-24 overflow-hidden">
                <div class="aspect-[4/5] w-full overflow-hidden bg-beige-100">
                    <img src="{{ $event->poster_url }}" alt="Poster {{ $event->title }}" class="size-full object-cover">
                </div>
                <div class="p-6">
                    <x-badge color="gray" class="!bg-beige-100 !text-cocoa-700">
                        <x-icon :name="$event->category->icon" class="size-3.5" /> {{ $event->category->name }}
                    </x-badge>
                    <h3 class="mt-3 text-xl font-semibold text-cocoa-900">{{ $event->title }}</h3>
                    <p class="mt-1 text-xs text-cocoa-500">Oleh {{ $event->organizer }}</p>

                    <dl class="mt-5 space-y-3 border-t border-beige-200/80 pt-4 text-sm">
                        <div class="flex items-start gap-2.5 text-cocoa-700">
                            <x-icon name="calendar" class="mt-0.5 size-4 shrink-0 text-ember-600" />
                            <span>{{ $event->schedule_label }}</span>
                        </div>
                        <div class="flex items-start gap-2.5 text-cocoa-700">
                            <x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-ember-600" />
                            <span>{{ $event->location }}, {{ $event->city }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-beige-200/80 pt-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Total Pembayaran</span>
                            <span class="font-display text-2xl font-semibold text-cocoa-900">{{ $event->price_label }}</span>
                        </div>
                    </dl>

                    <button type="submit" data-once class="btn btn-primary mt-6 w-full">
                        <x-icon name="ticket" class="size-5" /> Kirim Pendaftaran
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-outline mt-2.5 w-full">Kembali ke Detail Event</a>
                </div>
            </div>
        </aside>
    </form>
</section>
@endsection
