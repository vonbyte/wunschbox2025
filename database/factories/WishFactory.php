<?php

namespace Database\Factories;

use App\Models\Wish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wish>
 */
class WishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'receiver' => fake()->randomElement(['Sonia', 'Tanja']),
            'is_public' => false,
            'status' => 'idea',
            'sortnr' => 0,
        ];
    }

    /**
     * A public wish on the owner's wish note
     */
    public function onWishNote(): static
    {
        return $this->state(fn () => [
            'is_public' => true,
            'receiver' => Wish::owner(),
        ]);
    }
}
