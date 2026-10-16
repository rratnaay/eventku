<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'events' => Event::count(),
            'users' => User::where('role', User::ROLE_USER)->count(),
            'registrations' => Registration::where('status', '!=', Registration::STATUS_CANCELLED)->count(),
            'active' => Event::where('status', Event::STATUS_PUBLISHED)->where('ends_at', '>=', now())->count(),
            'finished' => Event::where('ends_at', '<', now())->count(),
        ];

        // Pendaftaran per hari selama 14 hari terakhir (data nyata dari database).
        $from = Carbon::today()->subDays(13);
        $perDay = Registration::where('registered_at', '>=', $from)
            ->selectRaw('DATE(registered_at) as day, count(*) as total')
            ->groupBy('day')->pluck('total', 'day');

        $chart = collect(range(0, 13))->map(function ($i) use ($from, $perDay) {
            $date = $from->copy()->addDays($i);

            return ['label' => $date->format('d/m'), 'total' => (int) ($perDay[$date->toDateString()] ?? 0)];
        });

        $latestEvents = Event::with('category')->withSeatCount()->latest()->take(5)->get();
        $latestRegistrations = Registration::with('user', 'event')->latest('registered_at')->take(6)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'latestEvents', 'latestRegistrations'));
    }
}
