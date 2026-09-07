<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Organization> */
class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' Dance Academy';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'contact_email' => fake()->unique()->companyEmail(),
            'settings' => ['default_access_months' => 3],
            'is_active' => true,
        ];
    }
}
