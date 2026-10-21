<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_return_successful_response(): void
    {
        $event = Event::factory()->create();

        foreach (['/', '/events', '/about', '/contact', '/login', '/register', "/events/{$event->slug}"] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_draft_events_are_hidden_from_public(): void
    {
        $draft = Event::factory()->draft()->create();

        $this->get("/events/{$draft->slug}")->assertNotFound();
        $this->get('/events')->assertDontSee($draft->title);
    }

    public function test_events_can_be_filtered_by_category_and_search(): void
    {
        $music = Category::factory()->create(['name' => 'Musik', 'slug' => 'musik']);
        $a = Event::factory()->for($music)->create(['title' => 'Konser Pagi']);
        $b = Event::factory()->create(['title' => 'Seminar Siang']);

        $this->get('/events?category=musik')->assertSee('Konser Pagi')->assertDontSee('Seminar Siang');
        $this->get('/events?q=Seminar')->assertSee('Seminar Siang')->assertDontSee('Konser Pagi');
    }
}
