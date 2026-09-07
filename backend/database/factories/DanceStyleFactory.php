<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DanceStyle;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DanceStyle> */
class DanceStyleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Salsa', 'Bachata', 'Kizomba', 'Merengue', 'Tango']);

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'is_active' => true,
        ];
    }
}
