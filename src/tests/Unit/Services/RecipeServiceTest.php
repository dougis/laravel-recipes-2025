<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RecipeService;
use App\Models\Recipe;
use App\Models\User;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Mockery;

class RecipeServiceTest extends TestCase
{
    protected $recipeRepository;
    protected $recipeService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the repository interface
        $this->recipeRepository = Mockery::mock(RecipeRepositoryInterface::class);
        $this->recipeService = new RecipeService($this->recipeRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_user_recipes_calls_repository_correctly()
    {
        // Arrange
        $userId = 'user123';
        $expectedRecipes = collect([
            ['_id' => 'recipe1', 'name' => 'Test Recipe 1'],
            ['_id' => 'recipe2', 'name' => 'Test Recipe 2'],
        ]);

        $this->recipeRepository
            ->shouldReceive('getUserRecipes')
            ->once()
            ->with($userId)
            ->andReturn($expectedRecipes);

        // Act
        $result = $this->recipeService->getUserRecipes($userId);

        // Assert
        $this->assertEquals($expectedRecipes, $result);
    }

    public function test_get_public_recipes_calls_repository_correctly()
    {
        // Arrange
        $expectedRecipes = collect([
            ['_id' => 'recipe1', 'name' => 'Public Recipe 1', 'is_private' => false],
            ['_id' => 'recipe2', 'name' => 'Public Recipe 2', 'is_private' => false],
        ]);

        $this->recipeRepository
            ->shouldReceive('getPublicRecipes')
            ->once()
            ->andReturn($expectedRecipes);

        // Act
        $result = $this->recipeService->getPublicRecipes();

        // Assert
        $this->assertEquals($expectedRecipes, $result);
    }

    public function test_get_recipe_calls_repository_find()
    {
        // Arrange
        $recipeId = 'recipe123';
        $expectedRecipe = new Recipe(['_id' => $recipeId, 'name' => 'Test Recipe']);

        $this->recipeRepository
            ->shouldReceive('find')
            ->once()
            ->with($recipeId)
            ->andReturn($expectedRecipe);

        // Act
        $result = $this->recipeService->getRecipe($recipeId);

        // Assert
        $this->assertEquals($expectedRecipe, $result);
    }

    public function test_create_recipe_adds_user_id_and_calls_repository()
    {
        // Arrange
        $userId = 'user123';
        $data = [
            'name' => 'New Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions'
        ];
        $expectedData = array_merge($data, ['user_id' => $userId]);
        $expectedRecipe = new Recipe($expectedData);

        $this->recipeRepository
            ->shouldReceive('create')
            ->once()
            ->with($expectedData)
            ->andReturn($expectedRecipe);

        // Act
        $result = $this->recipeService->createRecipe($userId, $data);

        // Assert
        $this->assertEquals($expectedRecipe, $result);
    }

    public function test_update_recipe_calls_repository_correctly()
    {
        // Arrange
        $recipeId = 'recipe123';
        $data = [
            'name' => 'Updated Recipe',
            'ingredients' => 'Updated ingredients'
        ];
        $expectedRecipe = new Recipe(array_merge($data, ['_id' => $recipeId]));

        $this->recipeRepository
            ->shouldReceive('update')
            ->once()
            ->with($recipeId, $data)
            ->andReturn($expectedRecipe);

        // Act
        $result = $this->recipeService->updateRecipe($recipeId, $data);

        // Assert
        $this->assertEquals($expectedRecipe, $result);
    }

    public function test_delete_recipe_calls_repository_correctly()
    {
        // Arrange
        $recipeId = 'recipe123';

        $this->recipeRepository
            ->shouldReceive('delete')
            ->once()
            ->with($recipeId)
            ->andReturn(true);

        // Act
        $result = $this->recipeService->deleteRecipe($recipeId);

        // Assert
        $this->assertTrue($result);
    }

    public function test_toggle_recipe_privacy_toggles_is_private_flag()
    {
        // Arrange
        $recipeId = 'recipe123';
        $recipe = Mockery::mock(Recipe::class);
        $recipe->is_private = false;
        
        $recipe->shouldReceive('save')->once();

        $this->recipeRepository
            ->shouldReceive('find')
            ->once()
            ->with($recipeId)
            ->andReturn($recipe);

        // Act
        $result = $this->recipeService->toggleRecipePrivacy($recipeId);

        // Assert
        $this->assertTrue($result->is_private);
    }

    public function test_toggle_recipe_privacy_returns_null_when_recipe_not_found()
    {
        // Arrange
        $recipeId = 'nonexistent';

        $this->recipeRepository
            ->shouldReceive('find')
            ->once()
            ->with($recipeId)
            ->andReturn(null);

        // Act
        $result = $this->recipeService->toggleRecipePrivacy($recipeId);

        // Assert
        $this->assertNull($result);
    }

    public function test_search_recipes_calls_repository_correctly()
    {
        // Arrange
        $query = 'chicken';
        $userId = 'user123';
        $expectedResults = collect([
            ['_id' => 'recipe1', 'name' => 'Chicken Recipe 1'],
            ['_id' => 'recipe2', 'name' => 'Chicken Recipe 2'],
        ]);

        $this->recipeRepository
            ->shouldReceive('searchRecipes')
            ->once()
            ->with($query, $userId)
            ->andReturn($expectedResults);

        // Act
        $result = $this->recipeService->searchRecipes($query, $userId);

        // Assert
        $this->assertEquals($expectedResults, $result);
    }

    public function test_user_can_access_recipe_returns_true_for_public_recipe()
    {
        // Arrange
        $userId = 'user123';
        $recipe = new Recipe(['_id' => 'recipe1', 'is_private' => false, 'user_id' => 'other_user']);

        // Act
        $result = $this->recipeService->userCanAccessRecipe($userId, $recipe);

        // Assert
        $this->assertTrue($result);
    }

    public function test_user_can_access_recipe_returns_true_for_recipe_owner()
    {
        // Arrange
        $userId = 'user123';
        $recipe = new Recipe(['_id' => 'recipe1', 'is_private' => true, 'user_id' => $userId]);

        // Act
        $result = $this->recipeService->userCanAccessRecipe($userId, $recipe);

        // Assert
        $this->assertTrue($result);
    }

    public function test_user_can_access_recipe_returns_true_for_admin()
    {
        // Arrange
        $userId = 'user123';
        $recipe = new Recipe(['_id' => 'recipe1', 'is_private' => true, 'user_id' => 'other_user']);
        
        $adminUser = Mockery::mock(User::class);
        $adminUser->shouldReceive('isAdmin')->andReturn(true);
        
        Auth::shouldReceive('user')->andReturn($adminUser);

        // Act
        $result = $this->recipeService->userCanAccessRecipe($userId, $recipe);

        // Assert
        $this->assertTrue($result);
    }

    public function test_user_can_access_recipe_returns_false_for_private_recipe_not_owned()
    {
        // Arrange
        $userId = 'user123';
        $recipe = new Recipe(['_id' => 'recipe1', 'is_private' => true, 'user_id' => 'other_user']);
        
        $normalUser = Mockery::mock(User::class);
        $normalUser->shouldReceive('isAdmin')->andReturn(false);
        
        Auth::shouldReceive('user')->andReturn($normalUser);

        // Act
        $result = $this->recipeService->userCanAccessRecipe($userId, $recipe);

        // Assert
        $this->assertFalse($result);
    }

    public function test_generate_recipe_text_formats_recipe_correctly()
    {
        // Arrange
        $source = (object) ['name' => 'Test Source'];
        $classification = (object) ['name' => 'Test Classification'];
        
        $recipe = new Recipe([
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
            'notes' => 'Test notes',
            'servings' => 4,
            'calories' => 300,
            'fat' => 10,
            'cholesterol' => 50,
            'sodium' => 400,
            'protein' => 20
        ]);
        
        $recipe->source = $source;
        $recipe->classification = $classification;

        // Act
        $result = $this->recipeService->generateRecipeText($recipe);

        // Assert
        $this->assertStringContainsString('RECIPE: Test Recipe', $result);
        $this->assertStringContainsString('Source: Test Source', $result);
        $this->assertStringContainsString('Classification: Test Classification', $result);
        $this->assertStringContainsString('Servings: 4', $result);
        $this->assertStringContainsString('INGREDIENTS:', $result);
        $this->assertStringContainsString('Test ingredients', $result);
        $this->assertStringContainsString('INSTRUCTIONS:', $result);
        $this->assertStringContainsString('Test instructions', $result);
        $this->assertStringContainsString('NOTES:', $result);
        $this->assertStringContainsString('Test notes', $result);
        $this->assertStringContainsString('NUTRITIONAL INFORMATION:', $result);
        $this->assertStringContainsString('Calories: 300', $result);
        $this->assertStringContainsString('Fat: 10g', $result);
        $this->assertStringContainsString('Cholesterol: 50mg', $result);
        $this->assertStringContainsString('Sodium: 400mg', $result);
        $this->assertStringContainsString('Protein: 20g', $result);
    }

    public function test_generate_recipe_text_handles_missing_optional_fields()
    {
        // Arrange
        $recipe = new Recipe([
            'name' => 'Simple Recipe',
            'ingredients' => 'Simple ingredients',
            'instructions' => 'Simple instructions'
        ]);

        // Act
        $result = $this->recipeService->generateRecipeText($recipe);

        // Assert
        $this->assertStringContainsString('RECIPE: Simple Recipe', $result);
        $this->assertStringContainsString('INGREDIENTS:', $result);
        $this->assertStringContainsString('Simple ingredients', $result);
        $this->assertStringContainsString('INSTRUCTIONS:', $result);
        $this->assertStringContainsString('Simple instructions', $result);
        
        // Should not contain optional sections
        $this->assertStringNotContainsString('Source:', $result);
        $this->assertStringNotContainsString('Classification:', $result);
        $this->assertStringNotContainsString('Servings:', $result);
        $this->assertStringNotContainsString('NOTES:', $result);
        $this->assertStringNotContainsString('NUTRITIONAL INFORMATION:', $result);
    }
}
