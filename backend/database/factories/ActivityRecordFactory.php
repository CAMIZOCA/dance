<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityRecord;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityRecord>
 */
class ActivityRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['class', 'agenda', 'archive', 'group', 'explore', 'profile', 'tenant', 'action']),
            'label' => fake()->sentence(3),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
