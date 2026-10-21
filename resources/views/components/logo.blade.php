@props(['light' => false])
<span {{ $attributes->class(['inline-flex items-center gap-2 font-display text-2xl font-bold', $light ? 'text-cream-50' : 'text-cocoa-900']) }}>
    <span class="grid size-9 place-items-center rounded-xl bg-ember-500 text-white shadow-soft">
        <x-icon name="ticket" class="size-5" />
    </span>
    Event<span class="text-ember-500">Ku</span>
</span>
