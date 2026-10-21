@php
    $flashes = collect(['success' => 'emerald', 'error' => 'red', 'status' => 'sky'])
        ->filter(fn ($c, $k) => session()->has($k));
@endphp
@if ($flashes->isNotEmpty() || $errors->has('flash'))
    <div class="fixed inset-x-0 top-20 z-50 mx-auto flex max-w-md flex-col gap-2 px-4" role="status" aria-live="polite">
        @foreach ($flashes as $key => $color)
            <div data-flash class="flex items-start gap-3 rounded-2xl border bg-white px-4 py-3 text-sm shadow-lift transition-opacity duration-300 animate-fade-up
                {{ $key === 'success' ? 'border-emerald-200 text-emerald-800' : ($key === 'error' ? 'border-red-200 text-red-700' : 'border-sky-200 text-sky-800') }}">
                <x-icon :name="$key === 'success' ? 'check' : 'x'" class="mt-0.5 size-5 shrink-0" />
                <p class="font-medium">{{ session($key) }}</p>
            </div>
        @endforeach
    </div>
@endif
