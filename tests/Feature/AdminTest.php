<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Festival Kopi Nusantara',
            'category_id' => Category::factory()->create()->id,
            'description' => 'Festival kopi terbesar dengan puluhan roaster lokal dan workshop seduh.',
            'organizer' => 'Komunitas Kopi',
            'location' => 'Gedung Serbaguna',
            'city' => 'Bandung',
            'starts_at' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(10)->addHours(4)->format('Y-m-d\TH:i'),
            'quota' => 100,
            'price' => 50000,
            'status' => 'published',
        ], $overrides);
    }

    public function test_guest_and_regular_user_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/events')->assertForbidden();
        $this->actingAs(User::factory()->create())->post('/admin/events', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('events', 0);
    }

    public function test_admin_can_view_dashboard_with_real_stats(): void
    {
        Event::factory()->count(2)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Total Event');
    }

    public function test_admin_can_create_event_with_poster(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/events', $this->payload(['poster' => UploadedFile::fake()->image('poster.jpg', 600, 400)]))
            ->assertRedirect(route('admin.events.index'));

        $event = Event::where('title', 'Festival Kopi Nusantara')->firstOrFail();
        $this->assertSame('festival-kopi-nusantara', $event->slug);
        $this->assertSame($admin->id, $event->created_by);
        Storage::disk('public')->assertExists($event->poster);
    }

    public function test_event_creation_validates_input_and_poster_type(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/admin/events', $this->payload([
                'title' => '',
                'ends_at' => now()->subDay()->format('Y-m-d\TH:i'),
                'quota' => 0,
                'poster' => UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors(['title', 'ends_at', 'quota', 'poster']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_poster_larger_than_2mb_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/admin/events', $this->payload(['poster' => UploadedFile::fake()->image('big.jpg')->size(3000)]))
            ->assertSessionHasErrors('poster');
    }

    public function test_admin_can_update_event(): void
    {
        $event = Event::factory()->create(['title' => 'Judul Lama']);

        $this->actingAs(User::factory()->admin()->create())
            ->put("/admin/events/{$event->id}", $this->payload(['title' => 'Judul Baru', 'category_id' => $event->category_id]))
            ->assertRedirect(route('admin.events.index'));

        $this->assertSame('Judul Baru', $event->fresh()->title);
        $this->assertSame('judul-baru', $event->fresh()->slug);
    }

    public function test_quota_cannot_be_lowered_below_active_registrations(): void
    {
        $event = Event::factory()->create(['quota' => 10]);
        \App\Models\Registration::factory()->count(5)->create(['event_id' => $event->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->put("/admin/events/{$event->id}", $this->payload(['quota' => 3, 'category_id' => $event->category_id]))
            ->assertSessionHasErrors('quota');

        $this->assertSame(10, $event->fresh()->quota);
    }

    public function test_admin_can_delete_event(): void
    {
        $event = Event::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete("/admin/events/{$event->id}")
            ->assertRedirect(route('admin.events.index'));

        $this->assertModelMissing($event);
    }

    public function test_admin_can_manage_categories_and_used_category_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/categories', ['name' => 'Fotografi', 'icon' => 'camera'])
            ->assertRedirect(route('admin.categories.index'));
        $category = Category::where('name', 'Fotografi')->firstOrFail();
        $this->assertSame('fotografi', $category->slug);

        Event::factory()->for($category)->create();
        $this->actingAs($admin)->delete("/admin/categories/{$category->id}")->assertSessionHas('error');
        $this->assertModelExists($category);
    }

    public function test_admin_can_change_registration_status(): void
    {
        $registration = \App\Models\Registration::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch("/admin/registrations/{$registration->id}", ['status' => 'attended'])
            ->assertSessionHas('success');

        $this->assertSame('attended', $registration->fresh()->status);
    }
}
