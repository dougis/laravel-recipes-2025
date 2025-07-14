<?php

namespace Tests\Unit\Services;

use App\Models\Cookbook;
use App\Models\User;
use App\Repositories\Interfaces\CookbookRepositoryInterface;
use App\Services\CookbookService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class CookbookServiceTest extends TestCase
{
    protected $cookbookRepository;

    protected $cookbookService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the repository interface
        $this->cookbookRepository = Mockery::mock(CookbookRepositoryInterface::class);
        $this->cookbookService = new CookbookService($this->cookbookRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_user_cookbooks_calls_repository_correctly()
    {
        // Arrange
        $userId = 'user123';
        $expectedCookbooks = collect([
            ['_id' => 'cookbook1', 'name' => 'Test Cookbook 1'],
            ['_id' => 'cookbook2', 'name' => 'Test Cookbook 2'],
        ]);

        $this->cookbookRepository
            ->shouldReceive('getUserCookbooks')
            ->once()
            ->with($userId)
            ->andReturn($expectedCookbooks);

        // Act
        $result = $this->cookbookService->getUserCookbooks($userId);

        // Assert
        $this->assertEquals($expectedCookbooks, $result);
    }

    public function test_get_public_cookbooks_calls_repository_correctly()
    {
        // Arrange
        $expectedCookbooks = collect([
            ['_id' => 'cookbook1', 'name' => 'Public Cookbook 1', 'is_private' => false],
            ['_id' => 'cookbook2', 'name' => 'Public Cookbook 2', 'is_private' => false],
        ]);

        $this->cookbookRepository
            ->shouldReceive('getPublicCookbooks')
            ->once()
            ->andReturn($expectedCookbooks);

        // Act
        $result = $this->cookbookService->getPublicCookbooks();

        // Assert
        $this->assertEquals($expectedCookbooks, $result);
    }

    public function test_get_cookbook_calls_repository_find()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $expectedCookbook = new Cookbook(['_id' => $cookbookId, 'name' => 'Test Cookbook']);

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn($expectedCookbook);

        // Act
        $result = $this->cookbookService->getCookbook($cookbookId);

        // Assert
        $this->assertEquals($expectedCookbook, $result);
    }

    public function test_create_cookbook_adds_user_id_and_calls_repository()
    {
        // Arrange
        $userId = 'user123';
        $data = [
            'name' => 'New Cookbook',
            'description' => 'Test description',
        ];
        $expectedData = array_merge($data, ['user_id' => $userId]);
        $expectedCookbook = new Cookbook($expectedData);

        $this->cookbookRepository
            ->shouldReceive('create')
            ->once()
            ->with($expectedData)
            ->andReturn($expectedCookbook);

        // Act
        $result = $this->cookbookService->createCookbook($userId, $data);

        // Assert
        $this->assertEquals($expectedCookbook, $result);
    }

    public function test_update_cookbook_calls_repository_correctly()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $data = [
            'name' => 'Updated Cookbook',
            'description' => 'Updated description',
        ];
        $expectedCookbook = new Cookbook(array_merge($data, ['_id' => $cookbookId]));

        $this->cookbookRepository
            ->shouldReceive('update')
            ->once()
            ->with($cookbookId, $data)
            ->andReturn($expectedCookbook);

        // Act
        $result = $this->cookbookService->updateCookbook($cookbookId, $data);

        // Assert
        $this->assertEquals($expectedCookbook, $result);
    }

    public function test_delete_cookbook_calls_repository_correctly()
    {
        // Arrange
        $cookbookId = 'cookbook123';

        $this->cookbookRepository
            ->shouldReceive('delete')
            ->once()
            ->with($cookbookId)
            ->andReturn(true);

        // Act
        $result = $this->cookbookService->deleteCookbook($cookbookId);

        // Assert
        $this->assertTrue($result);
    }

    public function test_toggle_cookbook_privacy_toggles_is_private_flag()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $cookbook = Mockery::mock(Cookbook::class);
        $cookbook->is_private = false;

        $cookbook->shouldReceive('save')->once();

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn($cookbook);

        // Act
        $result = $this->cookbookService->toggleCookbookPrivacy($cookbookId);

        // Assert
        $this->assertTrue($result->is_private);
    }

    public function test_toggle_cookbook_privacy_returns_null_when_cookbook_not_found()
    {
        // Arrange
        $cookbookId = 'nonexistent';

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn(null);

        // Act
        $result = $this->cookbookService->toggleCookbookPrivacy($cookbookId);

        // Assert
        $this->assertNull($result);
    }

    public function test_add_recipes_to_cookbook_adds_new_recipes()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $recipeIds = ['recipe1', 'recipe2'];

        $cookbook = Mockery::mock(Cookbook::class);
        $cookbook->recipe_ids = [
            ['recipe_id' => 'existing_recipe', 'order' => 0],
        ];
        $cookbook->shouldReceive('save')->once();

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn($cookbook);

        // Act
        $result = $this->cookbookService->addRecipesToCookbook($cookbookId, $recipeIds);

        // Assert
        $this->assertCount(3, $result->recipe_ids);
        $this->assertEquals('recipe1', $result->recipe_ids[1]['recipe_id']);
        $this->assertEquals(1, $result->recipe_ids[1]['order']);
        $this->assertEquals('recipe2', $result->recipe_ids[2]['recipe_id']);
        $this->assertEquals(2, $result->recipe_ids[2]['order']);
    }

    public function test_add_recipes_to_cookbook_skips_existing_recipes()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $recipeIds = ['existing_recipe', 'new_recipe'];

        $cookbook = Mockery::mock(Cookbook::class);
        $cookbook->recipe_ids = [
            ['recipe_id' => 'existing_recipe', 'order' => 0],
        ];
        $cookbook->shouldReceive('save')->once();

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn($cookbook);

        // Act
        $result = $this->cookbookService->addRecipesToCookbook($cookbookId, $recipeIds);

        // Assert
        $this->assertCount(2, $result->recipe_ids);
        $this->assertEquals('existing_recipe', $result->recipe_ids[0]['recipe_id']);
        $this->assertEquals('new_recipe', $result->recipe_ids[1]['recipe_id']);
    }

    public function test_add_recipes_to_cookbook_returns_null_when_cookbook_not_found()
    {
        // Arrange
        $cookbookId = 'nonexistent';
        $recipeIds = ['recipe1'];

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn(null);

        // Act
        $result = $this->cookbookService->addRecipesToCookbook($cookbookId, $recipeIds);

        // Assert
        $this->assertNull($result);
    }

    public function test_remove_recipe_from_cookbook_removes_specified_recipe()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $recipeToRemove = 'recipe2';

        $cookbook = Mockery::mock(Cookbook::class);
        $cookbook->recipe_ids = [
            ['recipe_id' => 'recipe1', 'order' => 0],
            ['recipe_id' => 'recipe2', 'order' => 1],
            ['recipe_id' => 'recipe3', 'order' => 2],
        ];
        $cookbook->shouldReceive('save')->once();

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn($cookbook);

        // Act
        $result = $this->cookbookService->removeRecipeFromCookbook($cookbookId, $recipeToRemove);

        // Assert
        $this->assertCount(2, $result->recipe_ids);
        $this->assertEquals('recipe1', $result->recipe_ids[0]['recipe_id']);
        $this->assertEquals('recipe3', $result->recipe_ids[1]['recipe_id']);
    }

    public function test_remove_recipe_from_cookbook_returns_null_when_cookbook_not_found()
    {
        // Arrange
        $cookbookId = 'nonexistent';
        $recipeId = 'recipe1';

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn(null);

        // Act
        $result = $this->cookbookService->removeRecipeFromCookbook($cookbookId, $recipeId);

        // Assert
        $this->assertNull($result);
    }

    public function test_reorder_cookbook_recipes_updates_recipe_order()
    {
        // Arrange
        $cookbookId = 'cookbook123';
        $recipeOrder = [
            'recipe1' => 2,
            'recipe2' => 0,
            'recipe3' => 1,
        ];

        $cookbook = Mockery::mock(Cookbook::class);
        $cookbook->recipe_ids = [
            ['recipe_id' => 'recipe1', 'order' => 0],
            ['recipe_id' => 'recipe2', 'order' => 1],
            ['recipe_id' => 'recipe3', 'order' => 2],
        ];
        $cookbook->shouldReceive('save')->once();

        $this->cookbookRepository
            ->shouldReceive('find')
            ->once()
            ->with($cookbookId)
            ->andReturn($cookbook);

        // Act
        $result = $this->cookbookService->reorderCookbookRecipes($cookbookId, $recipeOrder);

        // Assert
        $this->assertEquals('recipe2', $result->recipe_ids[0]['recipe_id']);
        $this->assertEquals(0, $result->recipe_ids[0]['order']);
        $this->assertEquals('recipe3', $result->recipe_ids[1]['recipe_id']);
        $this->assertEquals(1, $result->recipe_ids[1]['order']);
        $this->assertEquals('recipe1', $result->recipe_ids[2]['recipe_id']);
        $this->assertEquals(2, $result->recipe_ids[2]['order']);
    }

    public function test_user_can_access_cookbook_returns_true_for_public_cookbook()
    {
        // Arrange
        $userId = 'user123';
        $cookbook = new Cookbook(['_id' => 'cookbook1', 'is_private' => false, 'user_id' => 'other_user']);

        // Act
        $result = $this->cookbookService->userCanAccessCookbook($userId, $cookbook);

        // Assert
        $this->assertTrue($result);
    }

    public function test_user_can_access_cookbook_returns_true_for_cookbook_owner()
    {
        // Arrange
        $userId = 'user123';
        $cookbook = new Cookbook(['_id' => 'cookbook1', 'is_private' => true, 'user_id' => $userId]);

        // Act
        $result = $this->cookbookService->userCanAccessCookbook($userId, $cookbook);

        // Assert
        $this->assertTrue($result);
    }

    public function test_user_can_access_cookbook_returns_true_for_admin()
    {
        // Arrange
        $userId = 'user123';
        $cookbook = new Cookbook(['_id' => 'cookbook1', 'is_private' => true, 'user_id' => 'other_user']);

        $adminUser = Mockery::mock(User::class);
        $adminUser->shouldReceive('isAdmin')->andReturn(true);

        Auth::shouldReceive('user')->andReturn($adminUser);

        // Act
        $result = $this->cookbookService->userCanAccessCookbook($userId, $cookbook);

        // Assert
        $this->assertTrue($result);
    }

    public function test_user_can_access_cookbook_returns_false_for_private_cookbook_not_owned()
    {
        // Arrange
        $userId = 'user123';
        $cookbook = new Cookbook(['_id' => 'cookbook1', 'is_private' => true, 'user_id' => 'other_user']);

        $normalUser = Mockery::mock(User::class);
        $normalUser->shouldReceive('isAdmin')->andReturn(false);

        Auth::shouldReceive('user')->andReturn($normalUser);

        // Act
        $result = $this->cookbookService->userCanAccessCookbook($userId, $cookbook);

        // Assert
        $this->assertFalse($result);
    }
}
