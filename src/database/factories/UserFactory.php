<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'subscription_tier' => 0, // Free tier by default
            'admin_override' => false,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a user with Tier 1 subscription
     */
    public function tier1(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_tier' => 1,
        ]);
    }

    /**
     * Create a user with Tier 2 subscription
     */
    public function tier2(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_tier' => 2,
        ]);
    }

    /**
     * Create an admin user
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_tier' => 100,
            'admin_override' => true,
        ]);
    }

    /**
     * Create an unverified user
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with a specific email
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }
}
