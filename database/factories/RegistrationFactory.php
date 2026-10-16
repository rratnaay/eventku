<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'code' => 'EVK-'.strtoupper(fake()->unique()->bothify('????####')),
            'status' => Registration::STATUS_CONFIRMED,
            'registered_at' => now(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => Registration::STATUS_CANCELLED]);
    }
}
