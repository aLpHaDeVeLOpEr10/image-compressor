<?php

namespace Database\Factories;

use App\Models\Tool;
use App\Models\ToolContentField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ToolContentField>
 */
class ToolContentFieldFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_id' => Tool::factory(),
            'key' => fake()->unique()->lexify('field_????'),
            'type' => 'text',
            'value' => fake()->sentence(),
            'position' => 0,
        ];
    }
}
