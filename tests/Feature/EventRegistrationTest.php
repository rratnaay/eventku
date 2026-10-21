<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_register(): void
    {
        $event = Event::factory()->create();

        $this->post("/events/{$event->slug}/register")->assertRedirect(route('login'));
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_user_can_register_and_see_it_in_my_registrations(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['title' => 'Workshop Laravel']);

        $response = $this->actingAs($user)->post("/events/{$event->slug}/register");

        $registration = Registration::firstOrFail();
        $response->assertRedirect(route('registrations.show', $registration));
        $this->assertSame($user->id, $registration->user_id);
        $this->assertSame('confirmed', $registration->status);
        $this->assertMatchesRegularExpression('/^EVK-[A-Z0-9]{8}$/', $registration->code);

        $this->actingAs($user)->get('/my-registrations')->assertOk()->assertSee('Workshop Laravel')->assertSee($registration->code);
    }

    public function test_duplicate_registration_is_prevented(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($user)->post("/events/{$event->slug}/register");
        $this->actingAs($user)->post("/events/{$event->slug}/register")->assertSessionHas('error');

        $this->assertSame(1, Registration::where('user_id', $user->id)->where('event_id', $event->id)->count());
    }

    public function test_registration_is_blocked_when_quota_is_full(): void
    {
        $event = Event::factory()->create(['quota' => 2]);
        Registration::factory()->count(2)->create(['event_id' => $event->id]);

        $this->actingAs(User::factory()->create())
            ->post("/events/{$event->slug}/register")
            ->assertSessionHas('error', 'Kuota event ini sudah penuh.');

        $this->assertSame(2, $event->registrations()->count());
    }

    public function test_cancelled_registrations_free_up_quota(): void
    {
        $event = Event::factory()->create(['quota' => 1]);
        Registration::factory()->cancelled()->create(['event_id' => $event->id]);

        $this->actingAs(User::factory()->create())->post("/events/{$event->slug}/register")->assertSessionHas('success');
        $this->assertSame(2, $event->registrations()->count());
    }

    public function test_registration_is_blocked_for_closed_finished_and_draft_events(): void
    {
        $user = User::factory()->create();

        foreach ([Event::factory()->closed(), Event::factory()->finished(), Event::factory()->draft()] as $factory) {
            $event = $factory->create();
            $this->actingAs($user)->post("/events/{$event->slug}/register")->assertSessionHas('error');
        }

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_user_can_cancel_and_register_again_with_same_record(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($user)->post("/events/{$event->slug}/register");
        $registration = Registration::firstOrFail();

        $this->actingAs($user)->patch("/my-registrations/{$registration->id}/cancel")->assertSessionHas('success');
        $this->assertSame('cancelled', $registration->fresh()->status);

        $this->actingAs($user)->post("/events/{$event->slug}/register")->assertSessionHas('success');
        $this->assertDatabaseCount('registrations', 1);
        $this->assertSame('confirmed', $registration->fresh()->status);
    }

    public function test_user_cannot_view_or_cancel_other_users_registration(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $registration = Registration::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get("/my-registrations/{$registration->id}")->assertForbidden();
        $this->actingAs($other)->patch("/my-registrations/{$registration->id}/cancel")->assertForbidden();
        $this->assertSame('confirmed', $registration->fresh()->status);

        $this->actingAs($owner)->get("/my-registrations/{$registration->id}")->assertOk()->assertSee($registration->code);
    }

    public function test_my_registrations_lists_only_own_data(): void
    {
        $user = User::factory()->create();
        $mine = Registration::factory()->create(['user_id' => $user->id]);
        $theirs = Registration::factory()->create();

        $this->actingAs($user)->get('/my-registrations')
            ->assertSee($mine->code)
            ->assertDontSee($theirs->code);
    }

    public function test_db_unique_constraint_blocks_duplicates_even_if_service_is_bypassed(): void
    {
        $registration = Registration::factory()->create();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Registration::factory()->create(['user_id' => $registration->user_id, 'event_id' => $registration->event_id]);
    }
}
