<?php

namespace Tests\Unit\Models;

use App\Models\Classification;
use App\Models\Recipe;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test recipe model basic attributes and casts.
     */
    public function test_recipe_model_attributes_and_casts()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
            'servings' => 4,
            'calories' => 250,
            'fat' => 10.5,
            'protein' => 15.0,
            'marked' => true,
            'tags' => ['vegetarian', 'quick'],
            'meal_ids' => ['breakfast', 'lunch'],
            'is_private' => false,
        ]);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals('Test Recipe', $recipe->name);
        $this->assertEquals($user->id, $recipe->user_id);
        $this->assertIsInt($recipe->servings);
        $this->assertIsInt($recipe->calories);
        $this->assertIsFloat($recipe->fat);
        $this->assertIsFloat($recipe->protein);
        $this->assertIsBool($recipe->marked);
        $this->assertIsArray($recipe->tags);
        $this->assertIsArray($recipe->meal_ids);
        $this->assertIsBool($recipe->is_private);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'name',
            'ingredients',
            'instructions',
            'notes',
            'servings',
            'source_id',
            'classification_id',
            'date_added',
            'calories',
            'fat',
            'cholesterol',
            'sodium',
            'protein',
            'marked',
            'tags',
            'meal_ids',
            'preparation_ids',
            'course_ids',
            'is_private',
        ];

        $recipe = new Recipe;
        $this->assertEquals($fillable, $recipe->getFillable());
    }

    /**
     * Test toSearchableArray method.
     */
    public function test_to_searchable_array_method()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
            'tags' => ['vegetarian', 'quick'],
            'is_private' => false,
        ]);

        $searchableArray = $recipe->toSearchableArray();

        $expectedFields = ['name', 'ingredients', 'instructions', 'tags', 'user_id', 'is_private'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $searchableArray);
        }

        $this->assertEquals('Test Recipe', $searchableArray['name']);
        $this->assertEquals('Test ingredients', $searchableArray['ingredients']);
        $this->assertEquals('Test instructions', $searchableArray['instructions']);
        $this->assertEquals(['vegetarian', 'quick'], $searchableArray['tags']);
        $this->assertEquals($user->id, $searchableArray['user_id']);
        $this->assertFalse($searchableArray['is_private']);
    }

    /**
     * Test user relationship.
     */
    public function test_user_relationship()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
        ]);

        // Test relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $recipe->user());
        $this->assertEquals($user->id, $recipe->user->id);
    }

    /**
     * Test source relationship.
     */
    public function test_source_relationship()
    {
        $recipe = new Recipe;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $recipe->source());
    }

    /**
     * Test classification relationship.
     */
    public function test_classification_relationship()
    {
        $recipe = new Recipe;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $recipe->classification());
    }

    /**
     * Test meals relationship.
     */
    public function test_meals_relationship()
    {
        $recipe = new Recipe;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $recipe->meals());
    }

    /**
     * Test courses relationship.
     */
    public function test_courses_relationship()
    {
        $recipe = new Recipe;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $recipe->courses());
    }

    /**
     * Test preparations relationship.
     */
    public function test_preparations_relationship()
    {
        $recipe = new Recipe;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $recipe->preparations());
    }

    /**
     * Test cookbooks relationship.
     */
    public function test_cookbooks_relationship()
    {
        $recipe = new Recipe;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $recipe->cookbooks());
    }

    /**
     * Test array casts work correctly.
     */
    public function test_array_casts()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
            'tags' => ['vegetarian', 'quick', 'healthy'],
            'meal_ids' => ['breakfast', 'lunch'],
            'preparation_ids' => ['baked', 'grilled'],
            'course_ids' => ['main', 'side'],
        ]);

        // Test that arrays are properly cast
        $this->assertIsArray($recipe->tags);
        $this->assertIsArray($recipe->meal_ids);
        $this->assertIsArray($recipe->preparation_ids);
        $this->assertIsArray($recipe->course_ids);

        // Test array contents
        $this->assertCount(3, $recipe->tags);
        $this->assertContains('vegetarian', $recipe->tags);
        $this->assertCount(2, $recipe->meal_ids);
        $this->assertContains('breakfast', $recipe->meal_ids);
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $recipe = new Recipe;
        $this->assertEquals('mongodb', $recipe->getConnectionName());
        $this->assertEquals('recipes', $recipe->getTable());
    }

    /**
     * Test searchable trait is applied.
     */
    public function test_searchable_trait()
    {
        $recipe = new Recipe;
        $this->assertTrue(method_exists($recipe, 'toSearchableArray'));
        $this->assertTrue(method_exists($recipe, 'searchableAs'));
    }
}
