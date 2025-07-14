<?php

namespace Tests\Unit\Models;

use App\Models\Cookbook;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookbookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test cookbook model basic attributes and casts.
     */
    public function test_cookbook_model_attributes_and_casts()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Test Cookbook',
            'description' => 'A test cookbook',
            'cover_image' => 'test-cover.jpg',
            'recipe_ids' => [
                ['recipe_id' => 'recipe1', 'order' => 1],
                ['recipe_id' => 'recipe2', 'order' => 2],
            ],
            'is_private' => false,
        ]);

        $this->assertInstanceOf(Cookbook::class, $cookbook);
        $this->assertEquals('Test Cookbook', $cookbook->name);
        $this->assertEquals($user->id, $cookbook->user_id);
        $this->assertIsArray($cookbook->recipe_ids);
        $this->assertIsBool($cookbook->is_private);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'name',
            'description',
            'cover_image',
            'recipe_ids',
            'is_private',
        ];

        $cookbook = new Cookbook;
        $this->assertEquals($fillable, $cookbook->getFillable());
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

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Test Cookbook',
            'description' => 'A test cookbook',
        ]);

        // Test relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $cookbook->user());
        $this->assertEquals($user->id, $cookbook->user->id);
    }

    /**
     * Test recipes relationship.
     */
    public function test_recipes_relationship()
    {
        $cookbook = new Cookbook;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $cookbook->recipes());
    }

    /**
     * Test addRecipe method.
     */
    public function test_add_recipe_method()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Test Cookbook',
            'description' => 'A test cookbook',
            'recipe_ids' => [],
        ]);

        // Add recipe without specifying order
        $cookbook->addRecipe('recipe1');
        $this->assertCount(1, $cookbook->recipe_ids);
        $this->assertEquals('recipe1', $cookbook->recipe_ids[0]['recipe_id']);
        $this->assertEquals(0, $cookbook->recipe_ids[0]['order']);

        // Add recipe with specific order
        $cookbook->addRecipe('recipe2', 5);
        $this->assertCount(2, $cookbook->recipe_ids);
        $this->assertEquals('recipe2', $cookbook->recipe_ids[1]['recipe_id']);
        $this->assertEquals(5, $cookbook->recipe_ids[1]['order']);

        // Add another recipe without order (should be added to end)
        $cookbook->addRecipe('recipe3');
        $this->assertCount(3, $cookbook->recipe_ids);
        $this->assertEquals('recipe3', $cookbook->recipe_ids[2]['recipe_id']);
        $this->assertEquals(2, $cookbook->recipe_ids[2]['order']);
    }

    /**
     * Test removeRecipe method.
     */
    public function test_remove_recipe_method()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Test Cookbook',
            'description' => 'A test cookbook',
            'recipe_ids' => [
                ['recipe_id' => 'recipe1', 'order' => 1],
                ['recipe_id' => 'recipe2', 'order' => 2],
                ['recipe_id' => 'recipe3', 'order' => 3],
            ],
        ]);

        // Remove middle recipe
        $cookbook->removeRecipe('recipe2');
        $this->assertCount(2, $cookbook->recipe_ids);

        // Check that recipe2 is removed and array is reindexed
        $recipeIds = array_column($cookbook->recipe_ids, 'recipe_id');
        $this->assertContains('recipe1', $recipeIds);
        $this->assertNotContains('recipe2', $recipeIds);
        $this->assertContains('recipe3', $recipeIds);

        // Remove non-existent recipe (should not change anything)
        $cookbook->removeRecipe('recipe4');
        $this->assertCount(2, $cookbook->recipe_ids);
    }

    /**
     * Test reorderRecipes method.
     */
    public function test_reorder_recipes_method()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Test Cookbook',
            'description' => 'A test cookbook',
            'recipe_ids' => [
                ['recipe_id' => 'recipe1', 'order' => 1],
                ['recipe_id' => 'recipe2', 'order' => 2],
                ['recipe_id' => 'recipe3', 'order' => 3],
            ],
        ]);

        // Reorder recipes
        $newOrder = [
            'recipe3' => 1,
            'recipe1' => 2,
            'recipe2' => 3,
        ];

        $cookbook->reorderRecipes($newOrder);

        // Check that recipes are properly reordered
        $this->assertEquals('recipe3', $cookbook->recipe_ids[0]['recipe_id']);
        $this->assertEquals(1, $cookbook->recipe_ids[0]['order']);

        $this->assertEquals('recipe1', $cookbook->recipe_ids[1]['recipe_id']);
        $this->assertEquals(2, $cookbook->recipe_ids[1]['order']);

        $this->assertEquals('recipe2', $cookbook->recipe_ids[2]['recipe_id']);
        $this->assertEquals(3, $cookbook->recipe_ids[2]['order']);
    }

    /**
     * Test cookbook with empty recipe_ids.
     */
    public function test_cookbook_with_empty_recipe_ids()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Empty Cookbook',
            'description' => 'A cookbook with no recipes',
        ]);

        // Test adding recipe to empty cookbook
        $cookbook->addRecipe('recipe1');
        $this->assertCount(1, $cookbook->recipe_ids);
        $this->assertEquals('recipe1', $cookbook->recipe_ids[0]['recipe_id']);
        $this->assertEquals(0, $cookbook->recipe_ids[0]['order']);

        // Test removing from cookbook with one recipe
        $cookbook->removeRecipe('recipe1');
        $this->assertEmpty($cookbook->recipe_ids);
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

        $cookbook = Cookbook::create([
            'user_id' => $user->id,
            'name' => 'Test Cookbook',
            'recipe_ids' => [
                ['recipe_id' => 'recipe1', 'order' => 1],
                ['recipe_id' => 'recipe2', 'order' => 2],
            ],
        ]);

        // Test that recipe_ids is properly cast to array
        $this->assertIsArray($cookbook->recipe_ids);
        $this->assertCount(2, $cookbook->recipe_ids);
        $this->assertIsArray($cookbook->recipe_ids[0]);
        $this->assertArrayHasKey('recipe_id', $cookbook->recipe_ids[0]);
        $this->assertArrayHasKey('order', $cookbook->recipe_ids[0]);
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $cookbook = new Cookbook;
        $this->assertEquals('mongodb', $cookbook->getConnectionName());
        $this->assertEquals('cookbooks', $cookbook->getTable());
    }
}
