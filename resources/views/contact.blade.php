@extends('layouts.app')

@section('title', 'Kontak — EventKu')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <p class="eyebrow">Kontak</p>
            <h1 class="section-title mt-2">Ada pertanyaan? Kami siap membantu.</h1>
            <p class="mt-4 text-cocoa-600">Hubungi kami untuk pertanyaan seputar event, kerja sama penyelenggara, atau bantuan pendaftaran.</p>
            <ul class="mt-8 space-y-4">
                @foreach ([['mail', 'Email', 'halo@eventku.test'], ['phone', 'Telepon', '+62 21 5555 0123'], ['map-pin', 'Alamat', 'Jl. Jenderal Sudirman No. 10, Jakarta']] as [$icon, $label, $value])
                    <li class="card-beige flex items-center gap-4 p-4">
                        <span class="grid size-11 place-items-center rounded-2xl bg-white text-ember-600 shadow-soft"><x-icon :name="$icon" /></span>
                        <div><p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">{{ $label }}</p><p class="text-sm font-semibold text-cocoa-900">{{ $value }}</p></div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card flex flex-col justify-between p-7 sm:p-10 lg:col-span-3">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-beige-100 px-3.5 py-1 text-xs font-bold uppercase tracking-wider text-ember-600">
                    <x-icon name="sparkles" class="size-3.5" /> Saluran Resmi EventKu
                </span>
                <h2 class="mt-4 text-2xl font-semibold sm:text-3xl">Terhubung langsung dengan tim kami</h2>
                <p class="mt-2 text-sm leading-relaxed text-cocoa-600">
                    Pilih saluran komunikasi di bawah ini untuk menghubungi tim dukungan EventKu secara langsung. Kami siap merespons pada jam operasional Senin–Jumat pukul 09.00–18.00 WIB.
                </p>

                <div class="mt-8 grid gap-4">
                    <a href="mailto:halo@eventku.test"
                       class="group flex items-center justify-between gap-4 rounded-3xl border border-beige-200 bg-cream-50/80 p-5 transition duration-200 hover:-translate-y-0.5 hover:border-ember-400 hover:bg-white hover:shadow-lift">
                        <div class="flex items-center gap-4">
                            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-ember-500 text-white shadow-soft transition group-hover:bg-ember-600">
                                <x-icon name="mail" class="size-6" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Email Resmi EventKu</p>
                                <p class="mt-0.5 text-lg font-semibold text-cocoa-900 group-hover:text-ember-600">halo@eventku.test</p>
                                <p class="mt-0.5 text-xs text-cocoa-600">Untuk kerja sama media, kemitraan event, dan bantuan akun</p>
                            </div>
                        </div>
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-beige-100 text-cocoa-700 transition group-hover:bg-ember-500 group-hover:text-white">
                            <x-icon name="arrow-right" class="size-4" />
                        </span>
                    </a>

                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
                       class="group flex items-center justify-between gap-4 rounded-3xl border border-beige-200 bg-cream-50/80 p-5 transition duration-200 hover:-translate-y-0.5 hover:border-ember-400 hover:bg-white hover:shadow-lift">
                        <div class="flex items-center gap-4">
                            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-600 text-white shadow-soft transition group-hover:bg-emerald-700">
                                <x-icon name="whatsapp" class="size-6" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">WhatsApp Resmi</p>
                                <p class="mt-0.5 text-lg font-semibold text-cocoa-900 group-hover:text-ember-600">+62 812-3456-7890</p>
                                <p class="mt-0.5 text-xs text-cocoa-600">Klik untuk langsung membuka chat WhatsApp bersama tim support</p>
                            </div>
                        </div>
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-beige-100 text-cocoa-700 transition group-hover:bg-ember-500 group-hover:text-white">
                            <x-icon name="arrow-right" class="size-4" />
                        </span>
                    </a>

                    <a href="https://instagram.com/eventku.id" target="_blank" rel="noopener noreferrer"
                       class="group flex items-center justify-between gap-4 rounded-3xl border border-beige-200 bg-cream-50/80 p-5 transition duration-200 hover:-translate-y-0.5 hover:border-ember-400 hover:bg-white hover:shadow-lift">
                        <div class="flex items-center gap-4">
                            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-cocoa-800 text-cream-50 shadow-soft transition group-hover:bg-ember-600">
                                <x-icon name="instagram" class="size-6" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-cocoa-500">Instagram EventKu</p>
                                <p class="mt-0.5 text-lg font-semibold text-cocoa-900 group-hover:text-ember-600">@eventku.id</p>
                                <p class="mt-0.5 text-xs text-cocoa-600">Ikuti informasi event terbaru, highlight acara, dan pengumuman resmi</p>
                            </div>
                        </div>
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-beige-100 text-cocoa-700 transition group-hover:bg-ember-500 group-hover:text-white">
                            <x-icon name="arrow-right" class="size-4" />
                        </span>
                    </a>
                </div>
            </div>

            <div class="mt-8 rounded-2xl bg-beige-100/70 p-4 text-xs text-cocoa-600">
                <p class="font-semibold text-cocoa-800">Butuh konfirmasi pembayaran lebih cepat?</p>
                <p class="mt-1">Sertakan kode pendaftaran Anda (contoh: <code class="font-mono font-semibold">EVK-XXXXXXXX</code>) saat menghubungi kami melalui WhatsApp atau Email.</p>
            </div>
        </div>
    </div>
</section>
@endsection
