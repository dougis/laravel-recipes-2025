<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'ingredients' => $this->generateIngredients(),
            'instructions' => $this->generateInstructions(),
            'user_id' => User::factory(),
            'is_private' => false,
            'servings' => $this->faker->numberBetween(1, 12),
            'prep_time' => $this->faker->numberBetween(5, 60),
            'cook_time' => $this->faker->numberBetween(10, 180),
            'notes' => $this->faker->optional()->paragraph(),
            'source_id' => null,
            'classification_id' => null,
            'meal_id' => null,
            'course_id' => null,
            'preparation_id' => null,
            'tags' => $this->faker->words(3),
            'calories' => $this->faker->optional()->numberBetween(100, 800),
            'fat' => $this->faker->optional()->numberBetween(5, 50),
            'cholesterol' => $this->faker->optional()->numberBetween(0, 200),
            'sodium' => $this->faker->optional()->numberBetween(100, 2000),
            'protein' => $this->faker->optional()->numberBetween(5, 60),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a private recipe
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
    }

    /**
     * Create a public recipe
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => false,
        ]);
    }

    /**
     * Create a recipe for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->_id,
        ]);
    }

    /**
     * Create a recipe with nutritional information
     */
    public function withNutrition(): static
    {
        return $this->state(fn (array $attributes) => [
            'calories' => $this->faker->numberBetween(200, 600),
            'fat' => $this->faker->numberBetween(10, 30),
            'cholesterol' => $this->faker->numberBetween(20, 100),
            'sodium' => $this->faker->numberBetween(300, 1500),
            'protein' => $this->faker->numberBetween(15, 40),
        ]);
    }

    /**
     * Create a recipe with notes
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Generate realistic ingredients list
     */
    private function generateIngredients(): string
    {
        $ingredients = [];
        $count = $this->faker->numberBetween(3, 8);
        
        $commonIngredients = [
            '2 cups flour',
            '1 lb chicken breast',
            '3 eggs',
            '1/2 cup olive oil',
            '2 cloves garlic, minced',
            '1 onion, diced',
            '1 cup milk',
            '2 tbsp butter',
            '1 tsp salt',
            '1/2 tsp black pepper',
            '1 cup cheese, grated',
            '2 cups water',
            '1 tbsp sugar',
        ];

        for ($i = 0; $i < $count; $i++) {
            $ingredients[] = $this->faker->randomElement($commonIngredients);
        }

        return implode("\n", $ingredients);
    }

    /**
     * Generate realistic instructions
     */
    private function generateInstructions(): string
    {
        $steps = [];
        $count = $this->faker->numberBetween(3, 6);
        
        $commonSteps = [
            'Preheat oven to 350°F.',
            'Mix dry ingredients in a large bowl.',
            'In another bowl, whisk together wet ingredients.',
            'Combine wet and dry ingredients until just mixed.',
            'Pour into greased baking dish.',
            'Bake for 25-30 minutes until golden brown.',
            'Let cool before serving.',
            'Heat oil in a large skillet over medium heat.',
            'Add onions and cook until softened.',
            'Season with salt and pepper to taste.',
        ];

        for ($i = 0; $i < $count; $i++) {
            $steps[] = ($i + 1) . '. ' . $this->faker->randomElement($commonSteps);
        }

        return implode("\n", $steps);
    }
}