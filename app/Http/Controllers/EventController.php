<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:80'],
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:open,full,finished'],
            'sort' => ['nullable', 'in:soonest,latest,price_low'],
        ]);

        $query = Event::visible()->withSeatCount()->with('category');

        $query->when($filters['q'] ?? null, function ($q, $term) {
            $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('organizer', 'like', "%{$term}%"));
        });

        $query->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)));
        $query->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', $city));
        $query->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('starts_at', $date));

        match ($filters['status'] ?? null) {
            'open' => $query->upcoming()->where('status', Event::STATUS_PUBLISHED)->whereNot(fn ($q) => $q->full()),
            'full' => $query->upcoming()->full(),
            'finished' => $query->finished(),
            default => null,
        };

        match ($filters['sort'] ?? 'soonest') {
            'latest' => $query->latest(),
            'price_low' => $query->orderBy('price')->orderBy('starts_at'),
            default => $query->orderByRaw('ends_at < ? asc', [now()])->orderBy('starts_at'),
        };

        return view('events.index', [
            'events' => $query->paginate(9)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'cities' => Event::visible()->distinct()->orderBy('city')->pluck('city'),
            'filters' => $filters,
        ]);
    }

    public function show(Event $event)
    {
        abort_unless(in_array($event->status, [Event::STATUS_PUBLISHED, Event::STATUS_CLOSED]) || auth()->user()?->isAdmin(), 404);

        $event->loadCount([
            'registrations as active_registrations_count' => fn ($q) => $q->where('status', '!=', 'cancelled'),
        ])->load('category', 'creator');

        $registration = auth()->user()
            ? $event->registrations()->where('user_id', auth()->id())->first()
            : null;

        $related = Event::visible()->upcoming()->withSeatCount()->with('category')
            ->where('category_id', $event->category_id)->whereKeyNot($event->id)
            ->orderBy('starts_at')->take(3)->get();

        return view('events.show', compact('event', 'registration', 'related'));
    }
}
