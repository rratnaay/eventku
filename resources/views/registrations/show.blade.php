@extends('layouts.app')

@section('title', 'Detail Pendaftaran '.$registration->code.' — EventKu')

@section('content')
@php $event = $registration->event; @endphp
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('registrations.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-cocoa-600 hover:text-ember-600">← Kembali ke pendaftaran saya</a>

    <div class="card mt-5 overflow-hidden">
        <div class="grid gap-6 bg-beige-100/60 p-6 sm:grid-cols-3 sm:items-center sm:p-8">
            <div class="mx-auto w-44 overflow-hidden rounded-2xl border-4 border-white shadow-soft sm:mx-0 sm:w-full">
                <img src="{{ $event->poster_url }}" alt="Poster {{ $event->title }}" class="aspect-[4/5] w-full object-cover">
            </div>
            <div class="sm:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <x-badge :color="$registration->status_color" class="!px-4 !py-1.5 !text-sm">{{ $registration->status_label }}</x-badge>
                    <span class="text-xs text-cocoa-500">Terdaftar {{ $registration->registered_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>

                <h1 class="mt-3 text-2xl font-semibold sm:text-3xl">{{ $event->title }}</h1>
                <p class="mt-1 text-sm text-cocoa-600">Diselenggarakan oleh <strong class="text-cocoa-800">{{ $event->organizer }}</strong></p>

                <div class="mt-5 rounded-3xl border-2 border-dashed border-beige-300 bg-white p-4 text-center">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-cocoa-500">Kode Registrasi</p>
                    <p class="mt-1 font-mono text-2xl font-bold tracking-widest text-cocoa-900 sm:text-3xl">{{ $registration->code }}</p>
                    <p class="mt-1 text-xs text-cocoa-500">
                        @if ($registration->isConfirmed())
                            Pendaftaran telah terkonfirmasi. Tunjukkan kode ini saat registrasi ulang di lokasi.
                        @elseif ($registration->isPending())
                            Menunggu penyelesaian pembayaran / verifikasi oleh admin EventKu.
                        @else
                            Pendaftaran ini telah dibatalkan.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="p-7 sm:p-10">
            <dl class="grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Data Peserta</dt>
                    <dd class="mt-1 font-semibold text-cocoa-900">{{ $registration->participant_name ?: $registration->user->name }}</dd>
                    <dd class="text-sm text-cocoa-600">{{ $registration->participant_email ?: $registration->user->email }}</dd>
                    @if ($registration->participant_phone ?: $registration->user->phone)
                        <dd class="text-sm text-cocoa-600">WA: {{ $registration->participant_phone ?: $registration->user->phone }}</dd>
                    @endif
                    @if ($registration->participant_institution ?: $registration->user->institution)
                        <dd class="text-xs text-cocoa-500">{{ $registration->participant_institution ?: $registration->user->institution }}</dd>
                    @endif
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Jadwal Event</dt>
                    <dd class="mt-1 font-semibold text-cocoa-900">{{ $event->schedule_label }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Lokasi Event</dt>
                    <dd class="mt-1 font-semibold text-cocoa-900">{{ $event->location }}, {{ $event->city }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Informasi Pembayaran</dt>
                    <dd class="mt-1 font-semibold text-cocoa-900">{{ $event->price_label }} · {{ $registration->payment_status_label }}</dd>
                    <dd class="text-xs text-cocoa-600">Metode: {{ $registration->payment_method_label }}</dd>
                </div>
            </dl>

            @if ($registration->notes)
                <div class="mt-6 rounded-2xl bg-cream-50 p-4 text-sm text-cocoa-700">
                    <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Catatan Peserta</p>
                    <p class="mt-1">{{ $registration->notes }}</p>
                </div>
            @endif

            {{-- Bagian Pembayaran & Upload Bukti Pembayaran untuk Event Berbayar --}}
            @if (! $event->is_free && ! $registration->isCancelled())
                <div class="mt-8 rounded-3xl border border-beige-200 bg-cream-50/70 p-6">
                    <h2 class="text-lg font-semibold text-cocoa-900">Status & Bukti Pembayaran</h2>

                    @if ($registration->payment_proof)
                        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                            <a href="{{ $registration->payment_proof_url }}" target="_blank" rel="noopener noreferrer" class="block w-40 shrink-0 overflow-hidden rounded-2xl border border-beige-300 bg-white shadow-soft">
                                <img src="{{ $registration->payment_proof_url }}" alt="Bukti pembayaran {{ $registration->code }}" class="aspect-[4/3] w-full object-cover">
                            </a>
                            <div class="text-sm text-cocoa-700">
                                <p class="font-semibold text-cocoa-900">Bukti pembayaran telah diunggah</p>
                                <p class="mt-1 text-xs text-cocoa-600">
                                    @if ($registration->isConfirmed())
                                        Pembayaran Anda telah diverifikasi oleh admin pada {{ $registration->verified_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '-' }} WIB.
                                    @else
                                        Tim admin sedang memverifikasi bukti pembayaran Anda. Status pendaftaran akan berubah menjadi Terkonfirmasi setelah diverifikasi.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($registration->isPending())
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white p-3.5 text-xs">
                                <p class="font-bold uppercase tracking-wider text-cocoa-500">Transfer Bank</p>
                                <p class="mt-1 font-semibold text-cocoa-900">BCA 8720 1928 37 / Mandiri 1370 0098 1234 5</p>
                                <p class="text-cocoa-500">a.n. PT EventKu Indonesia</p>
                            </div>
                            <div class="rounded-2xl bg-white p-3.5 text-xs">
                                <p class="font-bold uppercase tracking-wider text-cocoa-500">QRIS / E-Wallet</p>
                                <p class="mt-1 font-semibold text-cocoa-900">GoPay / OVO / DANA: 0812-3456-7890</p>
                                <p class="text-cocoa-500">a.n. EventKu Official</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('registrations.payment', $registration) }}" enctype="multipart/form-data" class="mt-5 space-y-4 border-t border-beige-200 pt-5" novalidate>
                            @csrf
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="payment_method" class="label">Metode Pembayaran</label>
                                    <select id="payment_method" name="payment_method" class="input">
                                        @foreach (\App\Models\Registration::PAYMENT_METHODS as $key => $label)
                                            <option value="{{ $key }}" @selected(old('payment_method', $registration->payment_method) === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-error name="payment_method" />
                                </div>
                                <div>
                                    <label for="payment_proof" class="label">{{ $registration->payment_proof ? 'Perbarui Bukti Pembayaran' : 'Unggah Bukti Pembayaran' }}</label>
                                    <input id="payment_proof" type="file" name="payment_proof" accept="image/png,image/jpeg,image/webp" required
                                           class="input !p-2 text-xs file:mr-3 file:rounded-full file:border-0 file:bg-beige-100 file:px-3 file:py-1.5 file:font-semibold">
                                    <x-error name="payment_proof" />
                                </div>
                            </div>
                            <button type="submit" data-once class="btn btn-primary btn-sm">
                                <x-icon name="upload" class="size-4" /> {{ $registration->payment_proof ? 'Simpan Bukti Baru' : 'Unggah Bukti Pembayaran' }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
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
