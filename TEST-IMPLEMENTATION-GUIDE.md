# Test Implementation Guide

## Quick Start

This guide provides a step-by-step approach to implementing the test coverage plan for Laravel Recipes 2025.

## Phase 1: Foundation Setup (START HERE)

### 1. Test Infrastructure (COMPLETED ✅)

The following test infrastructure has been created:

- ✅ Enhanced `tests/TestCase.php` with helper methods
- ✅ Database factories for User, Recipe, and Cookbook models
- ✅ Base test structure and utilities

### 2. Available Helper Methods

The base `TestCase` class now provides these utility methods:

```php
// User Creation Helpers
$this->createFreeUser()       // Creates user with tier 0
$this->createTier1User()      // Creates user with tier 1  
$this->createTier2User()      // Creates user with tier 2
$this->createAdminUser()      // Creates admin user

// Authentication Helpers
$this->actingAsUser($user)    // Authenticate as specific user
$this->actingAsAdmin()        // Authenticate as admin

// Model Factories
$this->createRecipe($user, $attributes)   // Create test recipe
$this->createCookbook($user, $attributes) // Create test cookbook

// API Testing Helpers
$this->getApi('/recipes')     // GET /api/v1/recipes
$this->postApi('/recipes', $data)  // POST /api/v1/recipes
$this->putApi('/recipes/1', $data) // PUT /api/v1/recipes/1
$this->deleteApi('/recipes/1')     // DELETE /api/v1/recipes/1

// Response Assertions
$this->assertSuccessResponse($response)     // Asserts 200 + success
$this->assertErrorResponse($response, 400)  // Asserts error status
$this->assertUnauthorizedResponse($response) // Asserts 401
$this->assertForbiddenResponse($response)   // Asserts 403
```

### 3. Next Steps to Implement

#### 3.1 User Model Business Logic Tests

**File:** `tests/Unit/Models/UserTest.php` (exists but needs enhancement)

**Priority Tests to Add:**
```php
/** @test */
public function free_user_cannot_create_recipe_over_limit()
{
    $user = $this->createFreeUser();
    
    // Create 25 recipes (the limit)
    for ($i = 0; $i < 25; $i++) {
        $this->createRecipe($user);
    }
    
    // 26th recipe should fail
    $this->assertFalse($user->canCreateRecipe());
}

/** @test */
public function tier_1_user_has_unlimited_recipes_but_limited_cookbooks()
{
    $user = $this->createTier1User();
    
    // Should allow unlimited recipes
    for ($i = 0; $i < 30; $i++) {
        $this->assertTrue($user->canCreateRecipe());
        $this->createRecipe($user);
    }
    
    // Should allow 10 cookbooks max
    for ($i = 0; $i < 10; $i++) {
        $this->assertTrue($user->canCreateCookbook());
        $this->createCookbook($user);
    }
    
    // 11th cookbook should fail
    $this->assertFalse($user->canCreateCookbook());
}
```

#### 3.2 Recipe Privacy Service Tests

**File:** `tests/Unit/Services/RecipeServiceTest.php` (create new)

**Create this file with:**
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RecipeService;
use App\Models\Recipe;

class RecipeServiceTest extends TestCase
{
    private RecipeService $recipeService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->recipeService = app(RecipeService::class);
    }
    
    /** @test */
    public function user_can_access_own_recipe()
    {
        $user = $this->createUser();
        $recipe = $this->createRecipe($user, ['is_private' => true]);
        
        $this->assertTrue(
            $this->recipeService->userCanAccessRecipe($user->_id, $recipe)
        );
    }
    
    /** @test */
    public function user_cannot_access_others_private_recipe()
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $recipe = $this->createRecipe($owner, ['is_private' => true]);
        
        $this->assertFalse(
            $this->recipeService->userCanAccessRecipe($otherUser->_id, $recipe)
        );
    }
    
    /** @test */
    public function admin_can_access_any_recipe()
    {
        $owner = $this->createUser();
        $admin = $this->createAdminUser();
        $recipe = $this->createRecipe($owner, ['is_private' => true]);
        
        $this->assertTrue(
            $this->recipeService->userCanAccessRecipe($admin->_id, $recipe)
        );
    }
}
```

#### 3.3 Authentication API Tests

**File:** `tests/Feature/Api/V1/AuthControllerTest.php` (create new)

**Create this file with:**
```php
<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => ['user', 'token']
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'subscription_tier' => 0 // Should default to free tier
        ]);
    }
    
    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        
        $response = $this->postApi('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => ['user', 'token']
        ]);
    }
    
    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        
        $response = $this->postApi('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
        
        $this->assertErrorResponse($response, 401);
    }
}
```

## Running Tests

### Setup Commands

```bash
# Install dependencies (if not already done)
cd src
composer install

# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Models/UserTest.php

# Run tests with coverage
php artisan test --coverage

# Run only Unit tests
php artisan test --testsuite=Unit

# Run only Feature tests  
php artisan test --testsuite=Feature
```

### Test Database Setup

The tests are configured to use:
- Database: `laravel_recipes_test` (MongoDB)
- Environment: `testing`
- Cache/Session: `array` (in-memory)

## Implementation Priority

### Week 1 Priority (Phase 1)
1. ✅ Test infrastructure setup (DONE)
2. 🔄 Enhance existing `UserTest.php` with business logic tests
3. ⭐ Create `RecipeServiceTest.php` for privacy logic
4. ⭐ Create `AuthControllerTest.php` for authentication

### Week 2 Priority  
1. ⭐ `CookbookServiceTest.php` - Recipe management logic
2. ⭐ `RecipeControllerTest.php` - API endpoint tests
3. ⭐ `CookbookControllerTest.php` - API endpoint tests

### Week 3-4 Priority
1. Repository layer tests
2. Complex business logic tests
3. Integration tests

## Test Patterns to Follow

### 1. Arrange-Act-Assert Pattern
```php
/** @test */
public function it_does_something()
{
    // Arrange - Set up test data
    $user = $this->createTier2User();
    $recipe = $this->createRecipe($user);
    
    // Act - Perform the action
    $result = $this->recipeService->togglePrivacy($recipe, $user);
    
    // Assert - Verify the outcome
    $this->assertTrue($result);
    $this->assertTrue($recipe->fresh()->is_private);
}
```

### 2. API Testing Pattern
```php
/** @test */
public function api_endpoint_works_correctly()
{
    // Arrange
    $user = $this->createTier1User();
    $this->actingAs($user, 'sanctum');
    
    // Act
    $response = $this->postApi('/recipes', [
        'name' => 'Test Recipe',
        'ingredients' => 'Test ingredients',
        'instructions' => 'Test instructions'
    ]);
    
    // Assert
    $this->assertSuccessResponse($response);
    $response->assertJsonStructure([
        'data' => ['recipe' => ['id', 'name', 'ingredients']]
    ]);
}
```

### 3. Permission Testing Pattern
```php
/** @test */
public function only_authorized_users_can_access_endpoint()
{
    $recipe = $this->createRecipe();
    
    // Test unauthenticated access
    $response = $this->getApi("/recipes/{$recipe->_id}");
    $this->assertUnauthorizedResponse($response);
    
    // Test authenticated access
    $user = $this->actingAsUser();
    $response = $this->getApi("/recipes/{$recipe->_id}");
    $this->assertSuccessResponse($response);
}
```

## Common Test Scenarios

### Subscription Logic Tests
- Free user limits (25 recipes, 1 cookbook)
- Tier 1 limits (unlimited recipes, 10 cookbooks)  
- Tier 2 unlimited access
- Admin override functionality

### Privacy Control Tests
- Public recipe access (everyone)
- Private recipe access (owner + admin only)
- Privacy toggle (Tier 2+ only)
- Search result filtering

### API Security Tests
- Authentication required endpoints
- Authorization (user can only modify own content)
- Admin access override
- Rate limiting (if implemented)

### Data Validation Tests
- Required field validation
- Data type validation
- Business rule validation
- Unique constraint validation

## Debugging Test Issues

### Common Issues and Solutions

1. **Database not refreshing between tests**
   ```php
   // Ensure RefreshDatabase trait is used
   use RefreshDatabase;
   ```

2. **MongoDB connection issues**
   ```bash
   # Check MongoDB test database config
   php artisan config:show database.connections.mongodb
   ```

3. **Factory not found errors**
   ```php
   // Make sure model has factory defined
   protected static function newFactory()
   {
       return UserFactory::new();
   }
   ```

4. **Authentication not working in tests**
   ```php
   // Use Sanctum guard in tests
   $this->actingAs($user, 'sanctum');
   ```

## Next Implementation Steps

1. **Start with User model tests** - Most critical business logic
2. **Add RecipeService tests** - Core privacy functionality  
3. **Implement Auth API tests** - Security foundation
4. **Build up API coverage** - Recipe and Cookbook endpoints
5. **Add integration tests** - End-to-end workflows

Each test file should be implemented completely before moving to the next to ensure solid foundation building.