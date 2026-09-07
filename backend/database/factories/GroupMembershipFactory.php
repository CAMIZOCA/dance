<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GroupMembership;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GroupMembership> */
class GroupMembershipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'responsibility' => 'student',
            'status' => 'active',
            'started_at' => now()->startOfMonth(),
            'ended_at' => null,
        ];
    }
}
