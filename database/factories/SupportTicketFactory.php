<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model = SupportTicket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_user_id' => User::factory(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(SupportTicket::Categories),
            'priority' => SupportTicket::PriorityMedium,
            'status' => SupportTicket::StatusNew,
            'last_activity_at' => now(),
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'requester_user_id' => null,
        ]);
    }
}
