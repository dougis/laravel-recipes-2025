<?php

namespace Database\Factories;

use App\Models\Cookbook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CookbookFactory extends Factory
{
    protected $model = Cookbook::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true).' Cookbook',
            'description' => $this->faker->optional()->paragraph(),
            'user_id' => User::factory(),
            'is_private' => false,
            'recipe_ids' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a private cookbook
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
    }

    /**
     * Create a public cookbook
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => false,
        ]);
    }

    /**
     * Create a cookbook for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->_id,
        ]);
    }

    /**
     * Create a cookbook with a description
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a cookbook with recipe IDs
     */
    public function withRecipes(array $recipeIds): static
    {
        return $this->state(fn (array $attributes) => [
            'recipe_ids' => $recipeIds,
        ]);
    }

    /**
     * Create a cookbook with a specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }
}
