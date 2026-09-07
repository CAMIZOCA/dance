<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Level;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Level> */
class LevelFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Essential', 'Basic', 'Intermediate', 'Open', 'Ensemble']);

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
