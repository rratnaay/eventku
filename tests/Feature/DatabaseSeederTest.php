<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_idempotent_and_creates_valid_events_and_registrations(): void
    {
        $seeder = app(DatabaseSeeder::class);
        $seeder->run();

        $this->assertDatabaseCount('users', 26);
        $this->assertDatabaseCount('categories', 6);
        $this->assertDatabaseCount('events', 16);
        $this->assertDatabaseCount('registrations', 135);

        $events = Event::query()->with('category', 'creator')->get();
        foreach ($events as $event) {
            $this->assertNotNull($event->category);
            $this->assertNotNull($event->creator);

            $this->assertLessThanOrEqual(
                $event->quota,
                $event->registrations()->where('status', '!=', Registration::STATUS_CANCELLED)->count()
            );
        }

        $this->assertSame(
            0,
            Registration::query()
                ->whereHas('event', fn ($query) => $query
                    ->whereIn('status', [Event::STATUS_CLOSED, Event::STATUS_DRAFT, Event::STATUS_CANCELLED])
                    ->orWhere('ends_at', '<', now()))
                ->count()
        );

        $counts = [
            'users' => DB::table('users')->count(),
            'categories' => DB::table('categories')->count(),
            'events' => DB::table('events')->count(),
            'registrations' => DB::table('registrations')->count(),
        ];

        $seeder->run();

        $this->assertSame($counts, [
            'users' => DB::table('users')->count(),
            'categories' => DB::table('categories')->count(),
            'events' => DB::table('events')->count(),
            'registrations' => DB::table('registrations')->count(),
        ]);

        $publicEvent = Event::query()->where('title', 'Sunrise Fun Run 5K')->firstOrFail();
        $this->get('/events')->assertOk()->assertSee($publicEvent->title);
        $this->get("/events/{$publicEvent->slug}")->assertOk()->assertSee($publicEvent->category->name);
    }
}
