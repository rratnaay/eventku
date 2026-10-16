<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Event::visible()->upcoming()->withSeatCount()->with('category')
            ->where('is_featured', true)->orderBy('starts_at')->take(3)->get();

        $latest = Event::visible()->upcoming()->withSeatCount()->with('category')
            ->latest()->take(6)->get();

        $categories = Category::withCount(['events as events_count' => fn ($q) => $q->visible()->upcoming()])
            ->orderBy('name')->get();

        $stats = [
            'events' => Event::visible()->count(),
            'users' => User::where('role', User::ROLE_USER)->count(),
            'registrations' => Registration::where('status', '!=', Registration::STATUS_CANCELLED)->count(),
        ];

        $cities = Event::visible()->distinct()->orderBy('city')->pluck('city');

        return view('home', compact('featured', 'latest', 'categories', 'stats', 'cities'));
    }

    public function about()
    {
        return view('about');
    }
}
