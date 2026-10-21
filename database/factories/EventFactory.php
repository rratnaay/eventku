<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3, false);
        $start = fake()->dateTimeBetween('+3 days', '+60 days');

        return [
            'category_id' => Category::factory(),
            'created_by' => User::factory()->admin(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->paragraphs(3, true),
            'poster' => 'images/poster-music.jpg',
            'organizer' => fake()->company(),
            'location' => fake()->streetName().' Hall',
            'city' => fake()->randomElement(['Jakarta', 'Bandung', 'Yogyakarta', 'Surabaya', 'Malang']),
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+3 hours'),
            'quota' => 50,
            'price' => 0,
            'status' => Event::STATUS_PUBLISHED,
            'is_featured' => false,
        ];
    }

    public function paid(int $price = 75000): static
    {
        return $this->state(fn () => ['price' => $price]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => Event::STATUS_DRAFT]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => Event::STATUS_CLOSED]);
    }

    public function finished(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(10)->addHours(3),
        ]);
    }
}
