# Test Implementation Guide

## Quick Start

This guide provides a step-by-step approach to implementing the test coverage plan for Laravel Recipes 2025.

## Phase 1: Foundation Setup ✅ COMPLETED

### 1. Test Infrastructure ✅ COMPLETED

The following test infrastructure has been created:

- ✅ Enhanced `tests/TestCase.php` with helper methods
- ✅ Database factories for User, Recipe, and Cookbook models  
- ✅ Base test structure and utilities
- ✅ UserTest.php with comprehensive business logic validation
- ✅ AuthControllerTest.php with 25+ authentication test scenarios
- ✅ RecipeControllerTest.php with 40+ recipe API test scenarios

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

#### 3.1 User Model Business Logic Tests ✅ COMPLETED

**File:** `tests/Unit/Models/UserTest.php` ✅ COMPLETED

**Tests Implemented:** ✅ ALL CRITICAL BUSINESS LOGIC COVERED
```php
// Subscription tier validation ✅
test_has_tier1_access() ✅
test_has_tier2_access() ✅  
test_is_admin_method() ✅

// Recipe limits enforcement ✅
test_free_user_cannot_create_recipe_over_limit() ✅
test_tier_1_user_has_unlimited_recipes_but_limited_cookbooks() ✅
test_admin_user_bypasses_all_limits() ✅

// Cookbook limits enforcement ✅
test_free_user_cookbook_limit_enforcement() ✅
test_tier_1_user_has_unlimited_recipes_but_limited_cookbooks() ✅
test_admin_override_functionality() ✅

// Model validation and relationships ✅
test_user_model_attributes_and_casts() ✅
test_fillable_attributes() ✅
test_hidden_attributes() ✅
test_user_relationships() ✅
test_mongodb_connection() ✅
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

#### 3.3 Authentication API Tests ✅ COMPLETED

**File:** `tests/Feature/Api/V1/AuthControllerTest.php` ✅ COMPLETED

**Tests Implemented:** ✅ COMPREHENSIVE AUTH COVERAGE (25+ scenarios)
```php
// Registration Tests ✅
test_user_can_register_with_valid_data() ✅
test_user_cannot_register_with_invalid_email() ✅
test_user_cannot_register_with_duplicate_email() ✅
test_user_cannot_register_with_weak_password() ✅
test_user_cannot_register_with_mismatched_password_confirmation() ✅
test_registration_creates_free_tier_user_by_default() ✅
test_registration_returns_user_and_token() ✅

// Login Tests ✅
test_user_can_login_with_valid_credentials() ✅
test_user_cannot_login_with_invalid_email() ✅
test_user_cannot_login_with_wrong_password() ✅
test_login_returns_user_and_token() ✅
test_login_token_can_be_used_for_api_requests() ✅

// Logout Tests ✅
test_user_can_logout_with_valid_token() ✅
test_user_cannot_logout_without_token() ✅
test_logout_invalidates_token() ✅

// Token Management Tests ✅
test_token_provides_access_to_protected_endpoints() ✅
test_invalid_token_returns_unauthorized() ✅
test_token_includes_user_subscription_info() ✅
test_get_current_authenticated_user() ✅
test_get_current_user_fails_without_authentication() ✅

// Security Validation Tests ✅
test_registration_requires_all_fields() ✅
test_login_requires_email_and_password() ✅
test_malformed_email_validation() ✅
test_password_strength_requirements() ✅
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

### Phase 1 ✅ COMPLETED (Foundation + Critical Business Logic)
1. ✅ Test infrastructure setup 
2. ✅ Enhanced `UserTest.php` with comprehensive business logic tests
3. ✅ Created `AuthControllerTest.php` with 25+ authentication scenarios
4. ✅ Created `RecipeControllerTest.php` with 40+ recipe API scenarios

### Phase 2 Priority (In Progress - API Endpoint Coverage)
1. 🔄 **NEXT**: `CookbookControllerTest.php` - Cookbook API endpoint tests (Issue #23)
2. ⭐ `AdminControllerTest.php` - Admin API tests (Issue #24)
3. ⭐ Service layer tests for remaining business logic

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

## Current Status & Next Implementation Steps

### ✅ COMPLETED (Phase 1 + 2.1)
1. ✅ **User model tests** - Critical business logic fully validated
2. ✅ **Auth API tests** - Security foundation complete (25+ scenarios)
3. ✅ **Recipe API tests** - Complete endpoint coverage (40+ scenarios)

### 🔄 CURRENT PRIORITY (Phase 2.2)
1. **Cookbook API tests** - Next critical priority (Issue #23)
   - CRUD operations with ownership validation  
   - Recipe management (add/remove/reorder recipes)
   - Privacy controls and subscription enforcement
   - Export functionality testing

### 📋 UPCOMING (Phase 2.3-2.4)
1. **Admin API tests** - Admin functionality and override capabilities
2. **Service layer tests** - Remaining business logic validation
3. **Integration tests** - End-to-end workflow testing

**Test Coverage Progress**: ~60% complete with critical business logic and authentication fully validated. All core user-facing APIs (auth + recipes) comprehensively tested.