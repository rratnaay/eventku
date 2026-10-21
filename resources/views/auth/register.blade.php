@extends('layouts.app')

@section('title', 'Daftar Akun — EventKu')

@section('content')
<section class="mx-auto grid max-w-6xl items-stretch gap-0 overflow-hidden px-4 py-12 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-20">
    <div class="relative hidden overflow-hidden rounded-l-[2.5rem] lg:block">
        <img src="{{ asset('images/poster-music.jpg') }}" alt="" class="absolute inset-0 size-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-cocoa-900/85 via-cocoa-900/30 to-transparent"></div>
        <div class="absolute bottom-0 p-10 text-cream-50">
            <h2 class="font-display text-3xl font-semibold !text-cream-50">Bergabung bersama EventKu.</h2>
            <p class="mt-2 text-cocoa-100/90">Akun gratis. Daftar event favoritmu dalam beberapa klik.</p>
        </div>
    </div>

    <div class="card rounded-[2.5rem] p-8 sm:p-12 lg:rounded-l-none">
        <h1 class="text-3xl font-semibold">Buat akun</h1>
        <p class="mt-2 text-sm text-cocoa-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-ember-600 hover:underline">Masuk</a></p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5" novalidate>
            @csrf
            <div>
                <label for="name" class="label">Nama lengkap</label>
                <input id="name" name="name" value="{{ old('name') }}" class="input" required autofocus autocomplete="name">
                <x-error name="name" />
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autocomplete="email">
                <x-error name="email" />
            </div>
            <div>
                <label for="phone" class="label">Nomor telepon <span class="font-normal text-cocoa-400">(opsional)</span></label>
                <input id="phone" name="phone" value="{{ old('phone') }}" class="input" autocomplete="tel" inputmode="tel">
                <x-error name="phone" />
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">Kata sandi</label>
                    <input id="password" type="password" name="password" class="input" required autocomplete="new-password">
                    <x-error name="password" />
                </div>
                <div>
                    <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" required autocomplete="new-password">
                </div>
            </div>
            <p class="text-xs text-cocoa-500">Minimal 8 karakter, mengandung huruf dan angka.</p>
            <button type="submit" data-once class="btn btn-primary w-full">Daftar</button>
        </form>
    </div>
</section>
@endsection
