<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_register(): void
    {
        $event = Event::factory()->create();

        $this->get("/events/{$event->slug}/register")->assertRedirect(route('login'));
        $this->post("/events/{$event->slug}/register")->assertRedirect(route('login'));
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registration_form_is_prefilled_with_user_profile_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Rina Kartika',
            'email' => 'rina@example.com',
            'phone' => '081298765432',
            'institution' => 'Universitas Indonesia',
            'city' => 'Depok',
        ]);
        $event = Event::factory()->paid(150000)->create(['title' => 'Konser Senja']);

        $this->actingAs($user)
            ->get("/events/{$event->slug}/register")
            ->assertOk()
            ->assertSee('Formulir Pendaftaran')
            ->assertSee('Rina Kartika')
            ->assertSee('rina@example.com')
            ->assertSee('081298765432')
            ->assertSee('Universitas Indonesia')
            ->assertSee('Depok')
            ->assertSee('Rp 150.000');
    }

    public function test_user_can_register_and_see_it_in_my_registrations(): void
    {
        $user = User::factory()->create([
            'name' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'phone' => '081234567890',
        ]);
        $event = Event::factory()->create(['title' => 'Workshop Laravel']);

        $response = $this->actingAs($user)->post("/events/{$event->slug}/register");

        $registration = Registration::firstOrFail();
        $response->assertRedirect(route('registrations.index'));
        $this->assertSame($user->id, $registration->user_id);
        $this->assertSame('pending', $registration->status);
        $this->assertSame('Andi Pratama', $registration->participant_name);
        $this->assertSame('andi@example.com', $registration->participant_email);
        $this->assertSame('081234567890', $registration->participant_phone);
        $this->assertMatchesRegularExpression('/^EVK-[A-Z0-9]{8}$/', $registration->code);

        $this->actingAs($user)
            ->get('/my-registrations')
            ->assertOk()
            ->assertSee('Workshop Laravel')
            ->assertSee($registration->code)
            ->assertSee('Menunggu Verifikasi');
    }

    public function test_paid_event_payment_and_verification_flow(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->paid(100000)->create(['title' => 'Summit Teknologi']);

        // 1. User daftar belum unggah bukti bayar -> status pending (Menunggu Pembayaran)
        $this->actingAs($user)
            ->post("/events/{$event->slug}/register", [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '081234567899',
                'payment_method' => 'transfer_bank',
            ])
            ->assertRedirect(route('registrations.index'));

        $registration = Registration::firstOrFail();
        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertSame(Registration::PAYMENT_UNPAID, $registration->payment_status);
        $this->assertSame('Menunggu Pembayaran', $registration->status_label);

        // 2. User unggah bukti pembayaran -> status tetap pending (Menunggu Verifikasi)
        $this->actingAs($user)
            ->post(route('registrations.payment', $registration), [
                'payment_method' => 'qris',
                'payment_proof' => UploadedFile::fake()->image('bukti-bayar.jpg', 600, 800),
            ])
            ->assertRedirect(route('registrations.show', $registration));

        $registration->refresh();
        $this->assertSame(Registration::STATUS_PENDING, $registration->status);
        $this->assertSame(Registration::PAYMENT_WAITING_VERIFICATION, $registration->payment_status);
        $this->assertSame('Menunggu Verifikasi', $registration->status_label);
        Storage::disk('public')->assertExists($registration->payment_proof);

        // 3. Admin memverifikasi pembayaran -> status confirmed (Terkonfirmasi & Lunas)
        $this->actingAs($admin)
            ->patch(route('admin.registrations.update', $registration), [
                'status' => Registration::STATUS_CONFIRMED,
            ])
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame(Registration::STATUS_CONFIRMED, $registration->status);
        $this->assertSame(Registration::PAYMENT_PAID, $registration->payment_status);
        $this->assertSame('Terkonfirmasi', $registration->status_label);
        $this->assertNotNull($registration->verified_at);

        // 4. Filter di halaman Pendaftaran Saya berfungsi sesuai status
        $this->actingAs($user)
            ->get(route('registrations.index', ['status' => 'confirmed']))
            ->assertOk()
            ->assertSee($registration->code);

        $this->actingAs($user)
            ->get(route('registrations.index', ['status' => 'pending']))
            ->assertOk()
            ->assertDontSee($registration->code);
    }

    public function test_empty_state_is_shown_when_user_has_no_registrations(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/my-registrations')
            ->assertOk()
            ->assertSee('Belum ada pendaftaran')
            ->assertSee('Jelajah Event');
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
        $this->assertSame('pending', $registration->fresh()->status);
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

        $this->expectException(UniqueConstraintViolationException::class);
        Registration::factory()->create(['user_id' => $registration->user_id, 'event_id' => $registration->event_id]);
    }
}
