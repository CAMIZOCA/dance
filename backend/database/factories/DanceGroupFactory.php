<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\DanceGroup;
use App\Models\DanceStyle;
use App\Models\Level;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DanceGroup> */
class DanceGroupFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'organization_id' => Organization::factory(),
            'branch_id' => fn (array $attributes) => Branch::factory()->create(['organization_id' => $attributes['organization_id']])->id,
            'dance_style_id' => fn (array $attributes) => DanceStyle::factory()->create(['organization_id' => $attributes['organization_id']])->id,
            'level_id' => fn (array $attributes) => Level::factory()->create(['organization_id' => $attributes['organization_id']])->id,
            'name' => str($name)->title(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'settings' => [
                'allow_student_uploads' => false,
                'student_uploads_require_approval' => true,
                'allow_comments' => true,
            ],
            'is_private' => true,
            'is_active' => true,
        ];
    }
}
