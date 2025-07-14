<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MongoDB\Laravel\Eloquent\Model;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're using the test database
        Model::unguard();

        // Clean database before each test (MongoDB compatible)
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        Model::reguard();
        parent::tearDown();
    }

    /**
     * Clean the database for testing (MongoDB compatible)
     */
    protected function cleanDatabase(): void
    {
        try {
            // Clear all collections using deleteMany instead of truncate to avoid transactions
            $collections = ['users', 'recipes', 'cookbooks', 'classifications', 'courses', 'meals', 'preparations', 'sources', 'subscriptions'];

            foreach ($collections as $collection) {
                \DB::connection('mongodb')->collection($collection)->deleteMany([]);
            }
        } catch (\Exception $e) {
            // If collections don't exist yet, that's fine
        }
    }

    /**
     * Create a test user with specified subscription tier
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create a free tier user (default)
     */
    protected function createFreeUser(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'subscription_tier' => 0,
            'admin_override' => false,
        ], $attributes));
    }

    /**
     * Create a Tier 1 user
     */
    protected function createTier1User(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'subscription_tier' => 1,
            'admin_override' => false,
        ], $attributes));
    }

    /**
     * Create a Tier 2 user
     */
    protected function createTier2User(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'subscription_tier' => 2,
            'admin_override' => false,
        ], $attributes));
    }

    /**
     * Create an admin user
     */
    protected function createAdminUser(array $attributes = []): User
    {
        return $this->createUser(array_merge([
            'subscription_tier' => 100,
            'admin_override' => true,
        ], $attributes));
    }

    /**
     * Authenticate as a user and return the user instance
     */
    protected function actingAsUser(?User $user = null): User
    {
        $user = $user ?: $this->createUser();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /**
     * Authenticate as admin and return the user instance
     */
    protected function actingAsAdmin(): User
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    /**
     * Create a recipe for testing
     */
    protected function createRecipe(?User $user = null, array $attributes = []): \App\Models\Recipe
    {
        $user = $user ?: $this->createUser();

        return \App\Models\Recipe::create(array_merge([
            'name' => 'Test Recipe',
            'ingredients' => 'Test ingredients',
            'instructions' => 'Test instructions',
            'user_id' => $user->_id,
            'is_private' => false,
            'servings' => 4,
        ], $attributes));
    }

    /**
     * Create a cookbook for testing
     */
    protected function createCookbook(?User $user = null, array $attributes = []): \App\Models\Cookbook
    {
        $user = $user ?: $this->createUser();

        return \App\Models\Cookbook::create(array_merge([
            'name' => 'Test Cookbook',
            'description' => 'Test cookbook description',
            'user_id' => $user->_id,
            'is_private' => false,
            'recipe_ids' => [],
        ], $attributes));
    }

    /**
     * Assert that the response contains a success status
     */
    protected function assertSuccessResponse($response): void
    {
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    /**
     * Assert that the response contains an error status
     */
    protected function assertErrorResponse($response, int $statusCode = 400): void
    {
        $response->assertStatus($statusCode);
        $response->assertJson(['status' => 'error']);
    }

    /**
     * Assert that the response is unauthorized
     */
    protected function assertUnauthorizedResponse($response): void
    {
        $this->assertErrorResponse($response, 401);
    }

    /**
     * Assert that the response is forbidden
     */
    protected function assertForbiddenResponse($response): void
    {
        $this->assertErrorResponse($response, 403);
    }

    /**
     * Assert that the response is not found
     */
    protected function assertNotFoundResponse($response): void
    {
        $this->assertErrorResponse($response, 404);
    }

    /**
     * Get API headers for JSON requests
     */
    protected function getApiHeaders(array $additional = []): array
    {
        return array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $additional);
    }

    /**
     * Make a JSON API request
     */
    protected function jsonApi(string $method, string $uri, array $data = [], array $headers = [])
    {
        return $this->json(
            $method,
            '/api/v1'.$uri,
            $data,
            array_merge($this->getApiHeaders(), $headers)
        );
    }

    /**
     * Make a GET request to the API
     */
    protected function getApi(string $uri, array $headers = [])
    {
        return $this->jsonApi('GET', $uri, [], $headers);
    }

    /**
     * Make a POST request to the API
     */
    protected function postApi(string $uri, array $data = [], array $headers = [])
    {
        return $this->jsonApi('POST', $uri, $data, $headers);
    }

    /**
     * Make a PUT request to the API
     */
    protected function putApi(string $uri, array $data = [], array $headers = [])
    {
        return $this->jsonApi('PUT', $uri, $data, $headers);
    }

    /**
     * Make a DELETE request to the API
     */
    protected function deleteApi(string $uri, array $headers = [])
    {
        return $this->jsonApi('DELETE', $uri, [], $headers);
    }
}
