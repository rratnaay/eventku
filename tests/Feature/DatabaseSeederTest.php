<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_is_idempotent_and_does_not_create_demo_participants_or_registrations(): void
    {
        $seeder = app(DatabaseSeeder::class);
        $seeder->run();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('categories', 6);
        $this->assertDatabaseCount('events', 16);
        $this->assertDatabaseCount('registrations', 0);

        $this->assertSame(
            0,
            User::query()
                ->where('name', 'like', 'Peserta Demo %')
                ->orWhere('email', 'like', 'participant%@eventku.test')
                ->count()
        );

        $events = Event::query()->with('category', 'creator')->get();
        foreach ($events as $event) {
            $this->assertNotNull($event->category);
            $this->assertNotNull($event->creator);
        }

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
