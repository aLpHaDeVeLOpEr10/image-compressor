<?php

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'png-to-jpg',
            'name' => fake()->words(3, true),
            'slug' => null,
            'status' => Tool::STATUS_PUBLISHED,
            'meta_title' => fake()->sentence(6),
            'meta_description' => fake()->sentence(20),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tool::STATUS_DRAFT,
        ]);
    }
}
