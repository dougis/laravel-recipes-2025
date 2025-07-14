<?php

namespace Tests\Feature\Api\V1;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use RefreshDatabase;

    // ===============================
    // CRUD OPERATION TESTS
    // ===============================

    /**
     * Test authenticated user can create recipe with valid data.
     */
    public function test_authenticated_user_can_create_recipe_with_valid_data()
    {
        $user = $this->actingAsUser();

        $recipeData = [
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients\nSecond ingredient',
            'instructions' => 'Test instructions\nStep 2',
            'servings' => 4,
            'prep_time' => 15,
            'cook_time' => 30,
        ];

        $response = $this->postApi('/recipes', $recipeData);

        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => [
                'recipe' => ['id', 'name', 'ingredients', 'instructions', 'servings'],
            ],
        ]);

        $this->assertDatabaseHas('recipes', [
            'name' => 'Test Recipe',
            'user_id' => $user->_id,
            'servings' => 4,
        ]);
    }

    /**
     * Test user cannot create recipe with invalid data.
     */
    public function test_user_cannot_create_recipe_with_invalid_data()
    {
        $user = $this->actingAsUser();

        // Test missing required fields
        $response = $this->postApi('/recipes', []);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'ingredients', 'instructions']);

        // Test invalid data types
        $response = $this->postApi('/recipes', [
            'name' => '',
            'ingredients' => '',
            'instructions' => '',
            'servings' => 'invalid',
            'prep_time' => -1,
        ]);

        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'ingredients', 'instructions', 'servings', 'prep_time']);
    }

    /**
     * Test free user cannot create recipe over limit.
     */
    public function test_free_user_cannot_create_recipe_over_limit()
    {
        $user = $this->createFreeUser();
        $this->actingAs($user, 'sanctum');

        // Create 25 recipes (the limit for free users)
        for ($i = 0; $i < 25; $i++) {
            $this->createRecipe($user, ['name' => "Recipe $i"]);
        }

        // Try to create 26th recipe
        $response = $this->postApi('/recipes', [
            'name' => 'Over Limit Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
        ]);

        $this->assertErrorResponse($response, 403);
        $response->assertJsonPath('message', 'Recipe limit reached for your subscription tier');
    }

    /**
     * Test tier 1 user can create unlimited recipes.
     */
    public function test_tier_1_user_can_create_unlimited_recipes()
    {
        $user = $this->createTier1User();
        $this->actingAs($user, 'sanctum');

        // Create more than free tier limit
        for ($i = 0; $i < 30; $i++) {
            $this->createRecipe($user);
        }

        // Should still be able to create more
        $response = $this->postApi('/recipes', [
            'name' => 'Unlimited Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
        ]);

        $this->assertSuccessResponse($response);
    }

    /**
     * Test user can retrieve own recipe.
     */
    public function test_user_can_retrieve_own_recipe()
    {
        $user = $this->actingAsUser();
        $recipe = $this->createRecipe($user, [
            'name' => 'My Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.name', 'My Private Recipe');
    }

    /**
     * Test user can retrieve public recipe.
     */
    public function test_user_can_retrieve_public_recipe()
    {
        $owner = $this->createUser();
        $viewer = $this->actingAsUser();

        $recipe = $this->createRecipe($owner, [
            'name' => 'Public Recipe',
            'is_private' => false,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.name', 'Public Recipe');
    }

    /**
     * Test user cannot retrieve others private recipe.
     */
    public function test_user_cannot_retrieve_others_private_recipe()
    {
        $owner = $this->createUser();
        $viewer = $this->actingAsUser();

        $recipe = $this->createRecipe($owner, [
            'name' => 'Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}");

        $this->assertForbiddenResponse($response);
    }

    /**
     * Test user can update own recipe.
     */
    public function test_user_can_update_own_recipe()
    {
        $user = $this->actingAsUser();
        $recipe = $this->createRecipe($user);

        $updateData = [
            'name' => 'Updated Recipe Name',
            'ingredients' => 'Updated ingredients',
            'instructions' => 'Updated instructions',
            'servings' => 6,
        ];

        $response = $this->putApi("/recipes/{$recipe->_id}", $updateData);

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.name', 'Updated Recipe Name');
        $response->assertJsonPath('data.recipe.servings', 6);
    }

    /**
     * Test user cannot update others recipe.
     */
    public function test_user_cannot_update_others_recipe()
    {
        $owner = $this->createUser();
        $otherUser = $this->actingAsUser();

        $recipe = $this->createRecipe($owner);

        $response = $this->putApi("/recipes/{$recipe->_id}", [
            'name' => 'Hacked Recipe',
        ]);

        $this->assertForbiddenResponse($response);
    }

    /**
     * Test user can delete own recipe.
     */
    public function test_user_can_delete_own_recipe()
    {
        $user = $this->actingAsUser();
        $recipe = $this->createRecipe($user);

        $response = $this->deleteApi("/recipes/{$recipe->_id}");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('message', 'Recipe deleted successfully');

        // Verify recipe is deleted
        $this->assertDatabaseMissing('recipes', ['_id' => $recipe->_id]);
    }

    /**
     * Test user cannot delete others recipe.
     */
    public function test_user_cannot_delete_others_recipe()
    {
        $owner = $this->createUser();
        $otherUser = $this->actingAsUser();

        $recipe = $this->createRecipe($owner);

        $response = $this->deleteApi("/recipes/{$recipe->_id}");

        $this->assertForbiddenResponse($response);

        // Verify recipe still exists
        $this->assertDatabaseHas('recipes', ['_id' => $recipe->_id]);
    }

    // ===============================
    // PRIVACY CONTROL TESTS
    // ===============================

    /**
     * Test tier 2 user can toggle recipe privacy.
     */
    public function test_tier_2_user_can_toggle_recipe_privacy()
    {
        $user = $this->createTier2User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user, ['is_private' => false]);

        $response = $this->putApi("/recipes/{$recipe->_id}/privacy");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.is_private', true);

        // Toggle back
        $response = $this->putApi("/recipes/{$recipe->_id}/privacy");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.is_private', false);
    }

    /**
     * Test tier 1 user cannot toggle recipe privacy.
     */
    public function test_tier_1_user_cannot_toggle_recipe_privacy()
    {
        $user = $this->createTier1User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        $response = $this->putApi("/recipes/{$recipe->_id}/privacy");

        $this->assertForbiddenResponse($response);
        $response->assertJsonPath('message', 'Privacy controls require Tier 2 subscription');
    }

    /**
     * Test free user cannot toggle recipe privacy.
     */
    public function test_free_user_cannot_toggle_recipe_privacy()
    {
        $user = $this->createFreeUser();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        $response = $this->putApi("/recipes/{$recipe->_id}/privacy");

        $this->assertForbiddenResponse($response);
        $response->assertJsonPath('message', 'Privacy controls require Tier 2 subscription');
    }

    /**
     * Test admin can toggle any recipe privacy.
     */
    public function test_admin_can_toggle_any_recipe_privacy()
    {
        $owner = $this->createUser();
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $recipe = $this->createRecipe($owner, ['is_private' => false]);

        $response = $this->putApi("/recipes/{$recipe->_id}/privacy");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.is_private', true);
    }

    /**
     * Test privacy toggle requires authentication.
     */
    public function test_privacy_toggle_requires_authentication()
    {
        $user = $this->createUser();
        $recipe = $this->createRecipe($user);

        $response = $this->putApi("/recipes/{$recipe->_id}/privacy");

        $this->assertUnauthorizedResponse($response);
    }

    // ===============================
    // SEARCH & FILTERING TESTS
    // ===============================

    /**
     * Test search returns matching public recipes.
     */
    public function test_search_returns_matching_public_recipes()
    {
        $this->createRecipe(null, [
            'name' => 'Chocolate Cake',
            'is_private' => false,
        ]);

        $this->createRecipe(null, [
            'name' => 'Vanilla Cake',
            'is_private' => false,
        ]);

        $this->createRecipe(null, [
            'name' => 'Beef Stew',
            'is_private' => false,
        ]);

        $response = $this->getApi('/recipes/search?query=cake');

        $this->assertSuccessResponse($response);

        $recipeNames = collect($response->json('data.recipes'))
            ->pluck('name')->toArray();

        $this->assertContains('Chocolate Cake', $recipeNames);
        $this->assertContains('Vanilla Cake', $recipeNames);
        $this->assertNotContains('Beef Stew', $recipeNames);
    }

    /**
     * Test search excludes private recipes from results.
     */
    public function test_search_excludes_private_recipes_from_results()
    {
        $publicRecipe = $this->createRecipe(null, [
            'name' => 'Public Chicken Recipe',
            'is_private' => false,
        ]);

        $privateRecipe = $this->createRecipe(null, [
            'name' => 'Private Chicken Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi('/recipes/search?query=chicken');

        $this->assertSuccessResponse($response);

        $recipeNames = collect($response->json('data.recipes'))
            ->pluck('name')->toArray();

        $this->assertContains('Public Chicken Recipe', $recipeNames);
        $this->assertNotContains('Private Chicken Recipe', $recipeNames);
    }

    /**
     * Test search includes own private recipes.
     */
    public function test_search_includes_own_private_recipes()
    {
        $user = $this->actingAsUser();

        $ownPrivateRecipe = $this->createRecipe($user, [
            'name' => 'My Private Chicken Recipe',
            'is_private' => true,
        ]);

        $othersPrivateRecipe = $this->createRecipe(null, [
            'name' => 'Others Private Chicken Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi('/recipes/search?query=chicken');

        $this->assertSuccessResponse($response);

        $recipeNames = collect($response->json('data.recipes'))
            ->pluck('name')->toArray();

        $this->assertContains('My Private Chicken Recipe', $recipeNames);
        $this->assertNotContains('Others Private Chicken Recipe', $recipeNames);
    }

    /**
     * Test search respects pagination.
     */
    public function test_search_respects_pagination()
    {
        // Create 15 public recipes
        for ($i = 1; $i <= 15; $i++) {
            $this->createRecipe(null, [
                'name' => "Test Recipe $i",
                'is_private' => false,
            ]);
        }

        $response = $this->getApi('/recipes/search?query=test&limit=10&page=1');

        $this->assertSuccessResponse($response);
        $this->assertCount(10, $response->json('data.recipes'));
        $response->assertJsonPath('data.pagination.current_page', 1);
        $response->assertJsonPath('data.pagination.per_page', 10);
        $response->assertJsonPath('data.pagination.total', 15);
    }

    /**
     * Test search handles empty results.
     */
    public function test_search_handles_empty_results()
    {
        $this->createRecipe(null, [
            'name' => 'Chocolate Cake',
            'is_private' => false,
        ]);

        $response = $this->getApi('/recipes/search?query=nonexistent');

        $this->assertSuccessResponse($response);
        $this->assertEmpty($response->json('data.recipes'));
        $response->assertJsonPath('data.pagination.total', 0);
    }

    /**
     * Test authenticated user can list own recipes with pagination.
     */
    public function test_authenticated_user_can_list_own_recipes_with_pagination()
    {
        $user = $this->actingAsUser();
        $otherUser = $this->createUser();

        // Create user's own recipes (mix of public and private)
        $this->createRecipe($user, [
            'name' => 'My Public Recipe',
            'is_private' => false,
        ]);

        $this->createRecipe($user, [
            'name' => 'My Private Recipe',
            'is_private' => true,
        ]);

        // Create another user's recipes (should not appear)
        $this->createRecipe($otherUser, [
            'name' => 'Others Public Recipe',
            'is_private' => false,
        ]);

        $this->createRecipe($otherUser, [
            'name' => 'Others Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi('/recipes/');

        $this->assertSuccessResponse($response);

        $recipes = $response->json('data.recipes');
        $this->assertCount(2, $recipes);

        $recipeNames = collect($recipes)->pluck('name')->toArray();
        $this->assertContains('My Public Recipe', $recipeNames);
        $this->assertContains('My Private Recipe', $recipeNames);
        $this->assertNotContains('Others Public Recipe', $recipeNames);
        $this->assertNotContains('Others Private Recipe', $recipeNames);
    }

    /**
     * Test user recipes endpoint supports pagination.
     */
    public function test_user_recipes_endpoint_supports_pagination()
    {
        $user = $this->actingAsUser();

        // Create 15 recipes for the user
        for ($i = 1; $i <= 15; $i++) {
            $this->createRecipe($user, [
                'name' => "User Recipe $i",
                'is_private' => $i % 2 === 0, // Mix of public and private
            ]);
        }

        $response = $this->getApi('/recipes/?limit=10&page=1');

        $this->assertSuccessResponse($response);
        $this->assertCount(10, $response->json('data.recipes'));
        $response->assertJsonPath('data.pagination.current_page', 1);
        $response->assertJsonPath('data.pagination.per_page', 10);
        $response->assertJsonPath('data.pagination.total', 15);

        // Test page 2
        $response = $this->getApi('/recipes/?limit=10&page=2');

        $this->assertSuccessResponse($response);
        $this->assertCount(5, $response->json('data.recipes'));
        $response->assertJsonPath('data.pagination.current_page', 2);
    }

    /**
     * Test user recipes endpoint requires authentication.
     */
    public function test_user_recipes_endpoint_requires_authentication()
    {
        $response = $this->getApi('/recipes/');

        $this->assertUnauthorizedResponse($response);
    }

    /**
     * Test public recipes endpoint returns only public recipes.
     */
    public function test_public_recipes_endpoint_returns_only_public_recipes()
    {
        $this->createRecipe(null, [
            'name' => 'Public Recipe 1',
            'is_private' => false,
        ]);

        $this->createRecipe(null, [
            'name' => 'Public Recipe 2',
            'is_private' => false,
        ]);

        $this->createRecipe(null, [
            'name' => 'Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi('/recipes/public');

        $this->assertSuccessResponse($response);

        $recipes = $response->json('data.recipes');
        $this->assertCount(2, $recipes);

        $recipeNames = collect($recipes)->pluck('name')->toArray();
        $this->assertContains('Public Recipe 1', $recipeNames);
        $this->assertContains('Public Recipe 2', $recipeNames);
        $this->assertNotContains('Private Recipe', $recipeNames);
    }

    /**
     * Test public recipes endpoint supports pagination.
     */
    public function test_public_recipes_endpoint_supports_pagination()
    {
        // Create 12 public recipes and 3 private ones
        for ($i = 1; $i <= 12; $i++) {
            $this->createRecipe(null, [
                'name' => "Public Recipe $i",
                'is_private' => false,
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            $this->createRecipe(null, [
                'name' => "Private Recipe $i",
                'is_private' => true,
            ]);
        }

        $response = $this->getApi('/recipes/public?limit=10&page=1');

        $this->assertSuccessResponse($response);
        $this->assertCount(10, $response->json('data.recipes'));
        $response->assertJsonPath('data.pagination.current_page', 1);
        $response->assertJsonPath('data.pagination.per_page', 10);
        $response->assertJsonPath('data.pagination.total', 12); // Only public recipes counted
    }

    /**
     * Test public recipes endpoint does not require authentication.
     */
    public function test_public_recipes_endpoint_does_not_require_authentication()
    {
        $this->createRecipe(null, [
            'name' => 'Public Recipe',
            'is_private' => false,
        ]);

        $response = $this->getApi('/recipes/public');

        $this->assertSuccessResponse($response);
        $this->assertNotEmpty($response->json('data.recipes'));
    }

    // ===============================
    // EXPORT FUNCTIONALITY TESTS
    // ===============================

    /**
     * Test tier 1 user can export recipe as pdf.
     */
    public function test_tier_1_user_can_export_recipe_as_pdf()
    {
        $user = $this->createTier1User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        $response = $this->getApi("/recipes/{$recipe->_id}/export/pdf");

        $this->assertSuccessResponse($response);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test tier 1 user can export recipe as txt.
     */
    public function test_tier_1_user_can_export_recipe_as_txt()
    {
        $user = $this->createTier1User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        $response = $this->getApi("/recipes/{$recipe->_id}/export/txt");

        $this->assertSuccessResponse($response);
        $response->assertHeader('Content-Type', 'text/plain');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test free user cannot export recipe.
     */
    public function test_free_user_cannot_export_recipe()
    {
        $user = $this->createFreeUser();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        $response = $this->getApi("/recipes/{$recipe->_id}/export/pdf");

        $this->assertForbiddenResponse($response);
        $response->assertJsonPath('message', 'Export functionality requires paid subscription');
    }

    /**
     * Test export requires recipe access.
     */
    public function test_export_requires_recipe_access()
    {
        $owner = $this->createUser();
        $user = $this->createTier1User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($owner, ['is_private' => true]);

        $response = $this->getApi("/recipes/{$recipe->_id}/export/pdf");

        $this->assertForbiddenResponse($response);
    }

    /**
     * Test export returns correct content type.
     */
    public function test_export_returns_correct_content_type()
    {
        $user = $this->createTier2User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        // Test PDF export
        $pdfResponse = $this->getApi("/recipes/{$recipe->_id}/export/pdf");
        $this->assertSuccessResponse($pdfResponse);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');

        // Test TXT export
        $txtResponse = $this->getApi("/recipes/{$recipe->_id}/export/txt");
        $this->assertSuccessResponse($txtResponse);
        $txtResponse->assertHeader('Content-Type', 'text/plain');
    }

    /**
     * Test export handles invalid format.
     */
    public function test_export_handles_invalid_format()
    {
        $user = $this->createTier1User();
        $this->actingAs($user, 'sanctum');

        $recipe = $this->createRecipe($user);

        $response = $this->getApi("/recipes/{$recipe->_id}/export/invalid");

        $this->assertErrorResponse($response, 400);
        $response->assertJsonPath('message', 'Invalid export format');
    }

    // ===============================
    // PRINT FUNCTIONALITY TESTS
    // ===============================

    /**
     * Test authenticated user can access print view of own recipe.
     */
    public function test_authenticated_user_can_access_print_view_of_own_recipe()
    {
        $user = $this->actingAsUser();
        $recipe = $this->createRecipe($user, [
            'name' => 'My Printable Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}/print");

        $this->assertSuccessResponse($response);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Test authenticated user can access print view of public recipe.
     */
    public function test_authenticated_user_can_access_print_view_of_public_recipe()
    {
        $owner = $this->createUser();
        $user = $this->actingAsUser();

        $recipe = $this->createRecipe($owner, [
            'name' => 'Public Printable Recipe',
            'is_private' => false,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}/print");

        $this->assertSuccessResponse($response);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Test unauthenticated user can access print view of public recipe.
     */
    public function test_unauthenticated_user_can_access_print_view_of_public_recipe()
    {
        $recipe = $this->createRecipe(null, [
            'name' => 'Public Printable Recipe',
            'is_private' => false,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}/print");

        $this->assertSuccessResponse($response);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Test user cannot access print view of others private recipe.
     */
    public function test_user_cannot_access_print_view_of_others_private_recipe()
    {
        $owner = $this->createUser();
        $user = $this->actingAsUser();

        $recipe = $this->createRecipe($owner, [
            'name' => 'Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}/print");

        $this->assertForbiddenResponse($response);
    }

    /**
     * Test unauthenticated user cannot access print view of private recipe.
     */
    public function test_unauthenticated_user_cannot_access_print_view_of_private_recipe()
    {
        $recipe = $this->createRecipe(null, [
            'name' => 'Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}/print");

        $this->assertForbiddenResponse($response);
    }

    /**
     * Test admin can access print view of any recipe.
     */
    public function test_admin_can_access_print_view_of_any_recipe()
    {
        $owner = $this->createUser();
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $recipe = $this->createRecipe($owner, [
            'name' => 'Admin Accessible Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}/print");

        $this->assertSuccessResponse($response);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Test print endpoint handles non-existent recipe.
     */
    public function test_print_endpoint_handles_non_existent_recipe()
    {
        $user = $this->actingAsUser();
        $nonExistentId = '507f1f77bcf86cd799439011'; // Valid ObjectId format

        $response = $this->getApi("/recipes/{$nonExistentId}/print");

        $this->assertErrorResponse($response, 404);
    }

    /**
     * Test print endpoint handles invalid recipe ID.
     */
    public function test_print_endpoint_handles_invalid_recipe_id()
    {
        $user = $this->actingAsUser();
        $invalidId = 'invalid-id';

        $response = $this->getApi("/recipes/{$invalidId}/print");

        $this->assertErrorResponse($response, 400);
    }

    // ===============================
    // AUTHENTICATION & AUTHORIZATION TESTS
    // ===============================

    /**
     * Test unauthenticated user cannot create recipe.
     */
    public function test_unauthenticated_user_cannot_create_recipe()
    {
        $response = $this->postApi('/recipes', [
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
        ]);

        $this->assertUnauthorizedResponse($response);
    }

    /**
     * Test unauthenticated user can view public recipes.
     */
    public function test_unauthenticated_user_can_view_public_recipes()
    {
        $recipe = $this->createRecipe(null, [
            'name' => 'Public Recipe',
            'is_private' => false,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}");

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.name', 'Public Recipe');
    }

    /**
     * Test unauthenticated user cannot view private recipes.
     */
    public function test_unauthenticated_user_cannot_view_private_recipes()
    {
        $recipe = $this->createRecipe(null, [
            'name' => 'Private Recipe',
            'is_private' => true,
        ]);

        $response = $this->getApi("/recipes/{$recipe->_id}");

        $this->assertForbiddenResponse($response);
    }

    /**
     * Test authenticated user can only modify own recipes.
     */
    public function test_authenticated_user_can_only_modify_own_recipes()
    {
        $owner = $this->createUser();
        $otherUser = $this->actingAsUser();

        $recipe = $this->createRecipe($owner);

        // Cannot update
        $updateResponse = $this->putApi("/recipes/{$recipe->_id}", [
            'name' => 'Updated Name',
        ]);
        $this->assertForbiddenResponse($updateResponse);

        // Cannot delete
        $deleteResponse = $this->deleteApi("/recipes/{$recipe->_id}");
        $this->assertForbiddenResponse($deleteResponse);
    }

    /**
     * Test admin can access any recipe.
     */
    public function test_admin_can_access_any_recipe()
    {
        $owner = $this->createUser();
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $recipe = $this->createRecipe($owner, [
            'name' => 'Private Recipe',
            'is_private' => true,
        ]);

        // Admin can view private recipe
        $viewResponse = $this->getApi("/recipes/{$recipe->_id}");
        $this->assertSuccessResponse($viewResponse);

        // Admin can update recipe
        $updateResponse = $this->putApi("/recipes/{$recipe->_id}", [
            'name' => 'Admin Updated Recipe',
        ]);
        $this->assertSuccessResponse($updateResponse);
        $updateResponse->assertJsonPath('data.recipe.name', 'Admin Updated Recipe');
    }

    // ===============================
    // DATA VALIDATION TESTS
    // ===============================

    /**
     * Test recipe creation with maximum field lengths.
     */
    public function test_recipe_creation_with_maximum_field_lengths()
    {
        $user = $this->actingAsUser();

        $longString = str_repeat('a', 10000); // Very long string

        $response = $this->postApi('/recipes', [
            'name' => $longString,
            'ingredients' => $longString,
            'instructions' => $longString,
        ]);

        // Should either succeed or fail with validation error
        if ($response->status() === 422) {
            $response->assertJsonValidationErrors(['name']);
        } else {
            $this->assertSuccessResponse($response);
        }
    }

    /**
     * Test recipe creation with special characters.
     */
    public function test_recipe_creation_with_special_characters()
    {
        $user = $this->actingAsUser();

        $recipeData = [
            'name' => 'Recipe with émojis 🍰 & spëcial chars!',
            'ingredients' => 'Ingredients with ñ, ü, and other chars',
            'instructions' => 'Instructions with "quotes" and <html> tags',
            'servings' => 4,
        ];

        $response = $this->postApi('/recipes', $recipeData);

        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.recipe.name', 'Recipe with émojis 🍰 & spëcial chars!');
    }

    /**
     * Test recipe creation with boundary values.
     */
    public function test_recipe_creation_with_boundary_values()
    {
        $user = $this->actingAsUser();

        // Test minimum values
        $response = $this->postApi('/recipes', [
            'name' => 'A', // Minimum length
            'ingredients' => 'B',
            'instructions' => 'C',
            'servings' => 1,
            'prep_time' => 0,
            'cook_time' => 0,
        ]);

        $this->assertSuccessResponse($response);

        // Test maximum reasonable values
        $response = $this->postApi('/recipes', [
            'name' => str_repeat('Recipe Name ', 10),
            'ingredients' => str_repeat('Ingredient list ', 20),
            'instructions' => str_repeat('Instruction step ', 30),
            'servings' => 999,
            'prep_time' => 9999,
            'cook_time' => 9999,
        ]);

        $this->assertSuccessResponse($response);
    }

    /**
     * Test malformed JSON requests.
     */
    public function test_malformed_json_requests()
    {
        $user = $this->actingAsUser();

        // Send malformed JSON
        $response = $this->json('POST', '/api/v1/recipes', [], [
            'Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken,
            'Content-Type' => 'application/json',
        ], '{invalid-json}');

        $this->assertErrorResponse($response, 400);
    }

    /**
     * Test recipe not found handling.
     */
    public function test_recipe_not_found_handling()
    {
        $user = $this->actingAsUser();

        $nonExistentId = '507f1f77bcf86cd799439011'; // Valid ObjectId format

        $response = $this->getApi("/recipes/{$nonExistentId}");
        $this->assertErrorResponse($response, 404);

        $response = $this->putApi("/recipes/{$nonExistentId}", [
            'name' => 'Updated Name',
        ]);
        $this->assertErrorResponse($response, 404);

        $response = $this->deleteApi("/recipes/{$nonExistentId}");
        $this->assertErrorResponse($response, 404);
    }

    /**
     * Test invalid recipe ID format handling.
     */
    public function test_invalid_recipe_id_format_handling()
    {
        $user = $this->actingAsUser();

        $invalidId = 'invalid-id';

        $response = $this->getApi("/recipes/{$invalidId}");
        $this->assertErrorResponse($response, 400);

        $response = $this->putApi("/recipes/{$invalidId}", [
            'name' => 'Updated Name',
        ]);
        $this->assertErrorResponse($response, 400);
    }
}
