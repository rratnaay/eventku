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

    public function test_contact_page_displays_direct_contact_channels_without_message_form(): void
    {
        $response = $this->get('/contact');

        $response->assertOk()
            ->assertSee('mailto:halo@eventku.test', false)
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('https://instagram.com/eventku.id', false)
            ->assertDontSee('Kirim Pesan')
            ->assertDontSee('name="subject"', false)
            ->assertDontSee('name="message"', false);
    }

    public function test_event_posters_match_category_and_prioritize_custom_database_poster(): void
    {
        $cases = [
            ['Musik', 'musik', 'music', 'musik'],
            ['Kuliner', 'kuliner', 'cup', 'kuliner'],
            ['Olahraga', 'olahraga', 'run', 'olahraga'],
            ['Pendidikan', 'pendidikan', 'book', 'pendidikan'],
            ['Seni & Budaya', 'seni-budaya', 'palette', 'seni'],
            ['Teknologi', 'teknologi', 'cpu', 'teknologi'],
        ];

        foreach ($cases as [$name, $slug, $icon, $categoryKey]) {
            $category = Category::factory()->create(['name' => $name, 'slug' => $slug, 'icon' => $icon]);
            $event = Event::factory()->for($category)->create(['poster' => null]);

            $defaultPoster = $event->defaultPosterByCategory();
            $this->assertContains($defaultPoster, Event::CATEGORY_POSTERS[$categoryKey]);
            $this->assertStringContainsString($defaultPoster, $event->poster_url);
            $this->assertFileExists(public_path($defaultPoster));
        }

        $customEvent = Event::factory()->create(['poster' => 'posters/custom-event.jpg']);
        $this->assertStringContainsString('posters/custom-event.jpg', $customEvent->poster_url);
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
