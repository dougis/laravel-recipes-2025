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
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $user = new User();
        $this->assertEquals('mongodb', $user->getConnectionName());
        $this->assertEquals('users', $user->getTable());
    }
}
