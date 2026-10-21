@extends('layouts.app')

@section('title', 'Kontak — EventKu')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <p class="eyebrow">Kontak</p>
            <h1 class="section-title mt-2">Ada pertanyaan? Kami siap membantu.</h1>
            <p class="mt-4 text-cocoa-600">Kirim pesan untuk pertanyaan seputar event, kerja sama, atau kendala pendaftaran.</p>
            <ul class="mt-8 space-y-4">
                @foreach ([['mail', 'Email', 'halo@eventku.test'], ['phone', 'Telepon', '+62 21 5555 0123'], ['map-pin', 'Alamat', 'Jl. Jenderal Sudirman No. 10, Jakarta']] as [$icon, $label, $value])
                    <li class="card-beige flex items-center gap-4 p-4">
                        <span class="grid size-11 place-items-center rounded-2xl bg-white text-ember-600 shadow-soft"><x-icon :name="$icon" /></span>
                        <div><p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">{{ $label }}</p><p class="text-sm font-semibold text-cocoa-900">{{ $value }}</p></div>
                    </li>
                @endforeach
            </ul>
        </div>

        <form method="POST" action="{{ route('contact.store') }}" class="card space-y-5 p-7 lg:col-span-3" novalidate>
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Nama</label>
                    <input id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" class="input" required autocomplete="name">
                    <x-error name="name" />
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="input" required autocomplete="email">
                    <x-error name="email" />
                </div>
            </div>
            <div>
                <label for="subject" class="label">Subjek</label>
                <input id="subject" name="subject" value="{{ old('subject') }}" class="input" required>
                <x-error name="subject" />
            </div>
            <div>
                <label for="message" class="label">Pesan</label>
                <textarea id="message" name="message" rows="6" class="input" required>{{ old('message') }}</textarea>
                <x-error name="message" />
            </div>
            <button type="submit" data-once class="btn btn-primary">Kirim Pesan <x-icon name="arrow-right" class="size-4" /></button>
        </form>
    </div>
</section>
@endsection
