<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with('category')->withSeatCount()
            ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('title', 'like', "%{$t}%")->orWhere('city', 'like', "%{$t}%")))
            ->when($request->query('category'), fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.events.form', [
            'event' => new Event(['quota' => 100, 'price' => 0, 'status' => Event::STATUS_DRAFT]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(EventRequest $request)
    {
        $data = $this->payload($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        // created_by tidak mass-assignable; diisi eksplisit dari admin yang login.
        $event = new Event($data);
        $event->created_by = $request->user()->id;
        $event->save();

        return redirect()->route('admin.events.index')->with('success', "Event \"{$event->title}\" berhasil dibuat.");
    }

    public function edit(Event $event)
    {
        return view('admin.events.form', [
            'event' => $event->loadCount(['registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', 'cancelled')]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(EventRequest $request, Event $event)
    {
        $data = $this->payload($request, $event);

        if ($data['title'] !== $event->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $event->id);
        }

        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', "Event \"{$event->title}\" berhasil diperbarui.");
    }

    public function destroy(Event $event)
    {
        $title = $event->title;

        if ($event->poster && ! str_starts_with($event->poster, 'images/')) {
            Storage::disk('public')->delete($event->poster);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', "Event \"{$title}\" berhasil dihapus.");
    }

    private function payload(EventRequest $request, ?Event $event = null): array
    {
        $data = $request->safe()->except(['poster', 'is_featured']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('poster')) {
            // Hapus poster lama hasil upload (bukan aset bawaan).
            if ($event?->poster && ! str_starts_with($event->poster, 'images/')) {
                Storage::disk('public')->delete($event->poster);
            }
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $i = 2;

        while (Event::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
