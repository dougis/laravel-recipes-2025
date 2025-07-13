<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Cookbook;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user model basic attributes and casts.
     */
    public function test_user_model_attributes_and_casts()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
            'subscription_status' => 'active',
            'admin_override' => false,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertIsInt($user->subscription_tier);
        $this->assertIsBool($user->admin_override);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = [
            'name',
            'email',
            'password',
            'email_verified_at',
            'subscription_tier',
            'subscription_status',
            'subscription_expires_at',
            'admin_override',
            'stripe_customer_id',
            'stripe_subscription_id',
        ];

        $user = new User();
        $this->assertEquals($fillable, $user->getFillable());
    }

    /**
     * Test hidden attributes.
     */
    public function test_hidden_attributes()
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();
        $this->assertEquals($hidden, $user->getHidden());
    }

    /**
     * Test isAdmin method.
     */
    public function test_is_admin_method()
    {
        // Regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);
        $this->assertFalse($user->isAdmin());

        // Admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'subscription_tier' => 100,
        ]);
        $this->assertTrue($adminUser->isAdmin());
    }

    /**
     * Test hasTier2Access method.
     */
    public function test_has_tier2_access_method()
    {
        // Free tier user
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => false,
        ]);
        $this->assertFalse($freeUser->hasTier2Access());

        // Tier 1 user
        $tier1User = User::create([
            'name' => 'Tier 1 User',
            'email' => 'tier1@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
            'admin_override' => false,
        ]);
        $this->assertFalse($tier1User->hasTier2Access());

        // Tier 2 user
        $tier2User = User::create([
            'name' => 'Tier 2 User',
            'email' => 'tier2@example.com',
            'password' => 'password123',
            'subscription_tier' => 2,
            'admin_override' => false,
        ]);
        $this->assertTrue($tier2User->hasTier2Access());

        // User with admin override
        $overrideUser = User::create([
            'name' => 'Override User',
            'email' => 'override@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => true,
        ]);
        $this->assertTrue($overrideUser->hasTier2Access());
    }

    /**
     * Test hasTier1Access method.
     */
    public function test_has_tier1_access_method()
    {
        // Free tier user
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => false,
        ]);
        $this->assertFalse($freeUser->hasTier1Access());

        // Tier 1 user
        $tier1User = User::create([
            'name' => 'Tier 1 User',
            'email' => 'tier1@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
            'admin_override' => false,
        ]);
        $this->assertTrue($tier1User->hasTier1Access());

        // User with admin override
        $overrideUser = User::create([
            'name' => 'Override User',
            'email' => 'override@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => true,
        ]);
        $this->assertTrue($overrideUser->hasTier1Access());
    }

    /**
     * Test canCreateRecipe method.
     */
    public function test_can_create_recipe_method()
    {
        // Free tier user with no recipes
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => false,
        ]);
        $this->assertTrue($freeUser->canCreateRecipe());

        // Paid tier user always can create
        $paidUser = User::create([
            'name' => 'Paid User',
            'email' => 'paid@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
            'admin_override' => false,
        ]);
        $this->assertTrue($paidUser->canCreateRecipe());

        // User with admin override always can create
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => true,
        ]);
        $this->assertTrue($adminUser->canCreateRecipe());
    }

    /**
     * Test canCreateCookbook method.
     */
    public function test_can_create_cookbook_method()
    {
        // Free tier user with no cookbooks
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => false,
        ]);
        $this->assertTrue($freeUser->canCreateCookbook());

        // Tier 1 user with no cookbooks
        $tier1User = User::create([
            'name' => 'Tier 1 User',
            'email' => 'tier1@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
            'admin_override' => false,
        ]);
        $this->assertTrue($tier1User->canCreateCookbook());

        // Tier 2 user always can create
        $tier2User = User::create([
            'name' => 'Tier 2 User',
            'email' => 'tier2@example.com',
            'password' => 'password123',
            'subscription_tier' => 2,
            'admin_override' => false,
        ]);
        $this->assertTrue($tier2User->canCreateCookbook());

        // User with admin override always can create
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => true,
        ]);
        $this->assertTrue($adminUser->canCreateCookbook());
    }

    /**
     * Test user relationships.
     */
    public function test_user_relationships()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        // Test recipes relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->recipes());

        // Test cookbooks relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->cookbooks());
    }

    /**
     * Test recipe and cookbook count methods.
     */
    public function test_recipe_and_cookbook_count_methods()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'subscription_tier' => 1,
        ]);

        // Initially should be 0
        $this->assertEquals(0, $user->recipeCount());
        $this->assertEquals(0, $user->cookbookCount());
    }

    /**
     * Test free user cannot create recipe over limit.
     */
    public function test_free_user_cannot_create_recipe_over_limit()
    {
        $user = $this->createFreeUser();
        
        // Create 25 recipes (the limit)
        for ($i = 0; $i < 25; $i++) {
            $this->createRecipe($user);
        }
        
        // 26th recipe should fail
        $this->assertFalse($user->canCreateRecipe());
    }

    /**
     * Test tier 1 user has unlimited recipes but limited cookbooks.
     */
    public function test_tier_1_user_has_unlimited_recipes_but_limited_cookbooks()
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

    /**
     * Test tier 2 user has unlimited access.
     */
    public function test_tier_2_user_has_unlimited_access()
    {
        $user = $this->createTier2User();
        
        // Should allow unlimited recipes
        for ($i = 0; $i < 50; $i++) {
            $this->assertTrue($user->canCreateRecipe());
            $this->createRecipe($user);
        }
        
        // Should allow unlimited cookbooks
        for ($i = 0; $i < 20; $i++) {
            $this->assertTrue($user->canCreateCookbook());
            $this->createCookbook($user);
        }
    }

    /**
     * Test admin user bypasses all limits.
     */
    public function test_admin_user_bypasses_all_limits()
    {
        $user = $this->createAdminUser();
        
        // Should allow unlimited recipes
        for ($i = 0; $i < 50; $i++) {
            $this->assertTrue($user->canCreateRecipe());
            $this->createRecipe($user);
        }
        
        // Should allow unlimited cookbooks
        for ($i = 0; $i < 20; $i++) {
            $this->assertTrue($user->canCreateCookbook());
            $this->createCookbook($user);
        }
    }

    /**
     * Test free user cookbook limit enforcement.
     */
    public function test_free_user_cookbook_limit_enforcement()
    {
        $user = $this->createFreeUser();
        
        // Should allow 1 cookbook
        $this->assertTrue($user->canCreateCookbook());
        $this->createCookbook($user);
        
        // 2nd cookbook should fail
        $this->assertFalse($user->canCreateCookbook());
    }

    /**
     * Test admin override functionality.
     */
    public function test_admin_override_functionality()
    {
        $user = User::create([
            'name' => 'Override User',
            'email' => 'override@example.com',
            'password' => 'password123',
            'subscription_tier' => 0, // Free tier
            'admin_override' => true,
        ]);
        
        // Should have tier 1 access despite being free tier
        $this->assertTrue($user->hasTier1Access());
        
        // Should have tier 2 access despite being free tier
        $this->assertTrue($user->hasTier2Access());
        
        // Should bypass recipe limits
        for ($i = 0; $i < 30; $i++) {
            $this->assertTrue($user->canCreateRecipe());
            $this->createRecipe($user);
        }
        
        // Should bypass cookbook limits
        for ($i = 0; $i < 15; $i++) {
            $this->assertTrue($user->canCreateCookbook());
            $this->createCookbook($user);
        }
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $user = new User();
        $this->assertEquals('mongodb', $user->getConnectionName());
        $this->assertEquals('users', $user->getTable());
    }

    /**
     * Test user with null subscription tier (edge case).
     */
    public function test_user_with_null_subscription_tier()
    {
        $user = User::create([
            'name' => 'Null Tier User',
            'email' => 'null@example.com',
            'password' => 'password123',
            'subscription_tier' => null,
            'admin_override' => false,
        ]);

        // Null tier should be treated as free tier (0)
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->hasTier1Access());
        $this->assertFalse($user->hasTier2Access());
        $this->assertTrue($user->canCreateRecipe()); // Initially true
        $this->assertTrue($user->canCreateCookbook()); // Initially true
    }

    /**
     * Test exactly at recipe limit boundary (25th recipe).
     */
    public function test_free_user_exactly_at_recipe_limit()
    {
        $user = $this->createFreeUser();
        
        // Create exactly 24 recipes
        for ($i = 0; $i < 24; $i++) {
            $this->createRecipe($user);
        }
        
        // Should still be able to create the 25th recipe
        $this->assertTrue($user->canCreateRecipe());
        
        // Create the 25th recipe
        $this->createRecipe($user);
        
        // Now should not be able to create more
        $this->assertFalse($user->canCreateRecipe());
    }

    /**
     * Test exactly at cookbook limit boundary for Tier 1 user.
     */
    public function test_tier_1_user_exactly_at_cookbook_limit()
    {
        $user = $this->createTier1User();
        
        // Create exactly 9 cookbooks
        for ($i = 0; $i < 9; $i++) {
            $this->createCookbook($user);
        }
        
        // Should still be able to create the 10th cookbook
        $this->assertTrue($user->canCreateCookbook());
        
        // Create the 10th cookbook
        $this->createCookbook($user);
        
        // Now should not be able to create more
        $this->assertFalse($user->canCreateCookbook());
    }

    /**
     * Test subscription tier changes affecting permissions.
     */
    public function test_subscription_tier_changes_affect_permissions()
    {
        // Start as free user
        $user = User::create([
            'name' => 'Changing User',
            'email' => 'changing@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
            'admin_override' => false,
        ]);

        $this->assertFalse($user->hasTier1Access());
        $this->assertFalse($user->hasTier2Access());

        // Upgrade to Tier 1
        $user->subscription_tier = 1;
        $user->save();

        $this->assertTrue($user->hasTier1Access());
        $this->assertFalse($user->hasTier2Access());

        // Upgrade to Tier 2
        $user->subscription_tier = 2;
        $user->save();

        $this->assertTrue($user->hasTier1Access());
        $this->assertTrue($user->hasTier2Access());

        // Upgrade to Admin
        $user->subscription_tier = 100;
        $user->save();

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasTier1Access());
        $this->assertTrue($user->hasTier2Access());
    }

    /**
     * Test privacy toggle functionality for different tiers.
     */
    public function test_privacy_toggle_permissions_by_tier()
    {
        // Free user cannot use privacy features
        $freeUser = $this->createFreeUser();
        $this->assertFalse($freeUser->hasTier2Access());

        // Tier 1 user cannot use privacy features
        $tier1User = $this->createTier1User();
        $this->assertFalse($tier1User->hasTier2Access());

        // Tier 2 user can use privacy features
        $tier2User = $this->createTier2User();
        $this->assertTrue($tier2User->hasTier2Access());

        // Admin user can use privacy features
        $adminUser = $this->createAdminUser();
        $this->assertTrue($adminUser->hasTier2Access());
    }

    /**
     * Test admin override with low tier user.
     */
    public function test_admin_override_with_free_tier_user()
    {
        $user = User::create([
            'name' => 'Override Free User',
            'email' => 'override-free@example.com',
            'password' => 'password123',
            'subscription_tier' => 0, // Free tier
            'admin_override' => true, // But has admin override
        ]);

        // Should have access to all tier features despite being free tier
        $this->assertTrue($user->hasTier1Access());
        $this->assertTrue($user->hasTier2Access());
        $this->assertFalse($user->isAdmin()); // Not admin tier, just override

        // Should bypass all limits
        for ($i = 0; $i < 30; $i++) {
            $this->assertTrue($user->canCreateRecipe());
            $this->createRecipe($user);
        }

        for ($i = 0; $i < 15; $i++) {
            $this->assertTrue($user->canCreateCookbook());
            $this->createCookbook($user);
        }
    }

    /**
     * Test edge case: user with 0 vs null subscription tier.
     */
    public function test_zero_vs_null_subscription_tier()
    {
        $userWithZero = User::create([
            'name' => 'Zero Tier User',
            'email' => 'zero@example.com',
            'password' => 'password123',
            'subscription_tier' => 0,
        ]);

        $userWithNull = User::create([
            'name' => 'Null Tier User',
            'email' => 'null@example.com',
            'password' => 'password123',
            'subscription_tier' => null,
        ]);

        // Both should behave the same (as free tier)
        $this->assertEquals($userWithZero->hasTier1Access(), $userWithNull->hasTier1Access());
        $this->assertEquals($userWithZero->hasTier2Access(), $userWithNull->hasTier2Access());
        $this->assertEquals($userWithZero->canCreateRecipe(), $userWithNull->canCreateRecipe());
        $this->assertEquals($userWithZero->canCreateCookbook(), $userWithNull->canCreateCookbook());
    }
}
