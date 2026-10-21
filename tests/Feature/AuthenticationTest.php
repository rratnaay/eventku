<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_always_a_regular_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin', // upaya eskalasi hak akses harus diabaikan
        ]);

        $response->assertRedirect(route('home'));
        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame('rahasia123', $user->password);
    }

    public function test_registration_validates_input(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->post('/register', [
            'name' => '', 'email' => 'dup@example.com',
            'password' => 'pendek', 'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_user_can_login_and_is_redirected_home(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_for_private_pages(): void
    {
        $this->get('/my-registrations')->assertRedirect(route('login'));
    }
}
