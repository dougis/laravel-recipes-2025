<?php

namespace Tests\Unit\Models;

use App\Models\Subscription;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test subscription model basic attributes and casts.
     */
    public function test_subscription_model_attributes_and_casts()
    {
        $subscription = Subscription::create([
            'name' => 'Premium Plan',
            'description' => 'Access to all features',
            'price' => 9.99,
            'tier' => 2,
            'features' => ['unlimited_recipes', 'private_recipes', 'advanced_search'],
        ]);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals('Premium Plan', $subscription->name);
        $this->assertEquals('Access to all features', $subscription->description);
        $this->assertIsFloat($subscription->price);
        $this->assertEquals(9.99, $subscription->price);
        $this->assertIsInt($subscription->tier);
        $this->assertEquals(2, $subscription->tier);
        $this->assertIsArray($subscription->features);
        $this->assertCount(3, $subscription->features);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = [
            'name',
            'description',
            'price',
            'tier',
            'features',
        ];

        $subscription = new Subscription();
        $this->assertEquals($fillable, $subscription->getFillable());
    }

    /**
     * Test array casts work correctly.
     */
    public function test_array_casts()
    {
        $subscription = Subscription::create([
            'name' => 'Basic Plan',
            'description' => 'Basic features',
            'price' => 4.99,
            'tier' => 1,
            'features' => ['unlimited_recipes', 'export_pdf'],
        ]);

        // Test that features is properly cast to array
        $this->assertIsArray($subscription->features);
        $this->assertCount(2, $subscription->features);
        $this->assertContains('unlimited_recipes', $subscription->features);
        $this->assertContains('export_pdf', $subscription->features);
    }

    /**
     * Test subscription with zero price (free tier).
     */
    public function test_free_tier_subscription()
    {
        $subscription = Subscription::create([
            'name' => 'Free Plan',
            'description' => 'Limited features',
            'price' => 0.00,
            'tier' => 0,
            'features' => ['25_recipes', 'basic_search'],
        ]);

        $this->assertEquals(0.00, $subscription->price);
        $this->assertEquals(0, $subscription->tier);
        $this->assertIsFloat($subscription->price);
        $this->assertIsInt($subscription->tier);
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $subscription = new Subscription();
        $this->assertEquals('mongodb', $subscription->getConnectionName());
        $this->assertEquals('subscriptions', $subscription->getTable());
    }

    /**
     * Test features can be empty array.
     */
    public function test_empty_features_array()
    {
        $subscription = Subscription::create([
            'name' => 'Basic Plan',
            'description' => 'No special features',
            'price' => 1.99,
            'tier' => 1,
            'features' => [],
        ]);

        $this->assertIsArray($subscription->features);
        $this->assertEmpty($subscription->features);
    }
}
