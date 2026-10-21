@extends('layouts.app')

@section('title', 'Masuk — EventKu')

@section('content')
<section class="mx-auto grid max-w-6xl items-stretch gap-0 overflow-hidden px-4 py-12 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-20">
    <div class="relative hidden overflow-hidden rounded-l-[2.5rem] lg:block">
        <img src="{{ asset('images/hero.jpg') }}" alt="" class="absolute inset-0 size-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-cocoa-900/85 via-cocoa-900/30 to-transparent"></div>
        <div class="absolute bottom-0 p-10 text-cream-50">
            <h2 class="font-display text-3xl font-semibold !text-cream-50">Selamat datang kembali.</h2>
            <p class="mt-2 text-cocoa-100/90">Masuk untuk mendaftar event dan melihat pendaftaranmu.</p>
        </div>
    </div>

    <div class="card rounded-[2.5rem] p-8 sm:p-12 lg:rounded-l-none">
        <h1 class="text-3xl font-semibold">Masuk</h1>
        <p class="mt-2 text-sm text-cocoa-600">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-ember-600 hover:underline">Daftar sekarang</a></p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5" novalidate>
            @csrf
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required autofocus autocomplete="email">
                <x-error name="email" />
            </div>
            <div>
                <label for="password" class="label">Kata sandi</label>
                <input id="password" type="password" name="password" class="input" required autocomplete="current-password">
                <x-error name="password" />
            </div>
            <label class="flex items-center gap-2 text-sm text-cocoa-600">
                <input type="checkbox" name="remember" class="size-4 rounded border-beige-300 text-ember-500 focus:ring-ember-500"> Ingat saya
            </label>
            <button type="submit" data-once class="btn btn-primary w-full">Masuk</button>
        </form>

        <div class="mt-8 rounded-2xl bg-beige-100/70 p-4 text-xs text-cocoa-600">
            <p class="font-semibold text-cocoa-800">Akun demo (data seeder)</p>
            <p class="mt-1">Admin: <code>admin@eventku.test</code> · User: <code>user@eventku.test</code> · Sandi: <code>password</code></p>
        </div>
    </div>
</section>
@endsection
