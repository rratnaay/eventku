@extends('layouts.app')

@section('title', 'Tentang EventKu')
@section('description', 'EventKu menghubungkan peserta dengan event menarik dan membantu panitia mengelola pendaftaran.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid items-center gap-12 lg:grid-cols-2">
        <div class="reveal">
            <p class="eyebrow">Tentang kami</p>
            <h1 class="section-title mt-2">Event yang baik dimulai dari pendaftaran yang mudah.</h1>
            <p class="mt-5 text-lg leading-relaxed text-cocoa-600">EventKu lahir dari kebiasaan lama: mencari informasi acara di banyak tempat, mendaftar lewat formulir terpisah, lalu panitia kewalahan merekap data. Kami menyatukannya dalam satu platform yang sederhana dan hangat.</p>
            <p class="mt-4 leading-relaxed text-cocoa-600">Peserta dapat menemukan event, mendaftar, dan memantau statusnya. Panitia dapat mengelola event, kuota, dan pendaftar lewat dashboard yang rapi.</p>
            <a href="{{ route('events.index') }}" class="btn btn-primary mt-8">Mulai Jelajah <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <div class="reveal overflow-hidden rounded-[2.5rem] border-8 border-white shadow-lift">
            <img src="{{ asset('images/poster-art.jpg') }}" alt="Pameran budaya dengan kain batik" class="aspect-[4/3] w-full object-cover" loading="lazy">
        </div>
    </div>

    <div class="mt-20 grid gap-6 md:grid-cols-3">
        @foreach ([
            ['search', 'Mudah ditemukan', 'Cari berdasarkan kategori, kota, tanggal, dan status pendaftaran.'],
            ['shield', 'Kuota terjaga', 'Sistem mencegah pendaftaran ganda dan melewati kuota, bahkan saat banyak orang mendaftar bersamaan.'],
            ['chart', 'Mudah dikelola', 'Panitia memantau pendaftar dan mengubah status langsung dari dashboard.'],
        ] as [$icon, $title, $text])
            <div class="reveal card p-7">
                <span class="grid size-12 place-items-center rounded-2xl bg-beige-100 text-ember-600"><x-icon :name="$icon" class="size-6" /></span>
                <h2 class="mt-5 text-xl font-semibold">{{ $title }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-cocoa-600">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
