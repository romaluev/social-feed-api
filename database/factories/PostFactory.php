<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(rand(3, 8)),
            'content' => $this->faker->paragraphs(rand(3, 10), true),
            'hotness' => $this->faker->randomFloat(2, 0, 100),
            'view_count' => $this->faker->numberBetween(0, 1500),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /*
     * Hot post
     */
    public function trend(): static
    {
        return $this->state(fn (array $attributes) => [
            'hotness' => $this->faker->randomFloat(2, 70, 100),
            'view_count' => $this->faker->numberBetween(500, 800),
        ]);
    }

    /*
     * Cold post
     */
    public function cold(): static
    {
        return $this->state(fn (array $attributes) => [
            'hotness' => $this->faker->randomFloat(2, 0, 30),
            'view_count' => $this->faker->numberBetween(0, 100),
        ]);
    }

    /*
     * Trended post
     */
    public function overViewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => $this->faker->numberBetween(1001, 2000),
        ]);
    }

    /*
     * Post for user
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}

?>
