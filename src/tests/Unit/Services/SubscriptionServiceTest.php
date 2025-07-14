<?php

namespace Tests\Unit\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Mockery;
use Stripe\StripeClient;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    protected $subscriptionService;

    protected $stripeClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Stripe client
        $this->stripeClient = Mockery::mock(StripeClient::class);
        $this->subscriptionService = new SubscriptionService;

        // Replace the stripe client with our mock
        $reflection = new \ReflectionClass($this->subscriptionService);
        $stripeProperty = $reflection->getProperty('stripe');
        $stripeProperty->setAccessible(true);
        $stripeProperty->setValue($this->subscriptionService, $this->stripeClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_user_subscription_returns_null_for_nonexistent_user()
    {
        // Arrange
        User::shouldReceive('find')
            ->once()
            ->with('nonexistent_user')
            ->andReturn(null);

        // Act
        $result = $this->subscriptionService->getUserSubscription('nonexistent_user');

        // Assert
        $this->assertNull($result);
    }

    public function test_get_user_subscription_returns_subscription_data_with_database_subscription()
    {
        // Arrange
        $user = new User([
            '_id' => 'user123',
            'subscription_tier' => 1,
            'subscription_status' => 'active',
            'subscription_expires_at' => '2024-12-31 23:59:59',
            'admin_override' => false,
        ]);

        $subscription = new Subscription([
            'name' => 'Test Subscription',
            'description' => 'Test Description',
            'tier' => 1,
            'features' => ['Feature 1', 'Feature 2'],
        ]);

        User::shouldReceive('find')
            ->once()
            ->with('user123')
            ->andReturn($user);

        Subscription::shouldReceive('where')
            ->once()
            ->with('tier', 1)
            ->andReturnSelf();

        Subscription::shouldReceive('first')
            ->once()
            ->andReturn($subscription);

        // Act
        $result = $this->subscriptionService->getUserSubscription('user123');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('subscription', $result);
        $this->assertEquals(1, $result['user']['subscription_tier']);
        $this->assertEquals('active', $result['user']['subscription_status']);
        $this->assertEquals('Test Subscription', $result['subscription']->name);
    }

    public function test_get_user_subscription_creates_default_subscription_when_not_found_in_database()
    {
        // Arrange
        $user = new User([
            '_id' => 'user123',
            'subscription_tier' => 0,
            'subscription_status' => 'active',
            'subscription_expires_at' => null,
            'admin_override' => false,
        ]);

        User::shouldReceive('find')
            ->once()
            ->with('user123')
            ->andReturn($user);

        Subscription::shouldReceive('where')
            ->once()
            ->with('tier', 0)
            ->andReturnSelf();

        Subscription::shouldReceive('first')
            ->once()
            ->andReturn(null);

        // Act
        $result = $this->subscriptionService->getUserSubscription('user123');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('subscription', $result);
        $this->assertEquals(0, $result['user']['subscription_tier']);
        $this->assertEquals('Free Tier', $result['subscription']['name']);
        $this->assertIsArray($result['subscription']['features']);
    }

    public function test_update_user_subscription_throws_exception_for_nonexistent_user()
    {
        // Arrange
        User::shouldReceive('find')
            ->once()
            ->with('nonexistent_user')
            ->andReturn(null);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found');

        // Act
        $this->subscriptionService->updateUserSubscription('nonexistent_user', 1, 'pm_test');
    }

    public function test_update_user_subscription_to_free_tier_cancels_stripe_subscription()
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->stripe_subscription_id = 'sub_test123';
        $user->shouldReceive('save')->once();

        User::shouldReceive('find')
            ->once()
            ->with('user123')
            ->andReturn($user);

        // Mock Stripe subscriptions service
        $stripeSubscriptions = Mockery::mock();
        $stripeSubscriptions->shouldReceive('cancel')
            ->once()
            ->with('sub_test123');

        $this->stripeClient->subscriptions = $stripeSubscriptions;

        // Mock getUserSubscription call at the end
        $this->subscriptionService = Mockery::mock(SubscriptionService::class)->makePartial();
        $this->subscriptionService->shouldReceive('getUserSubscription')
            ->once()
            ->with('user123')
            ->andReturn(['test' => 'data']);

        // Replace stripe in the partial mock
        $reflection = new \ReflectionClass($this->subscriptionService);
        $stripeProperty = $reflection->getProperty('stripe');
        $stripeProperty->setAccessible(true);
        $stripeProperty->setValue($this->subscriptionService, $this->stripeClient);

        // Act
        $result = $this->subscriptionService->updateUserSubscription('user123', 0);

        // Assert
        $this->assertEquals(0, $user->subscription_tier);
        $this->assertEquals('active', $user->subscription_status);
        $this->assertNull($user->stripe_subscription_id);
        $this->assertEquals(['test' => 'data'], $result);
    }

    public function test_update_user_subscription_throws_exception_when_payment_method_missing_for_paid_tier()
    {
        // Arrange
        $user = new User(['_id' => 'user123']);

        User::shouldReceive('find')
            ->once()
            ->with('user123')
            ->andReturn($user);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment method ID is required for paid subscriptions');

        // Act
        $this->subscriptionService->updateUserSubscription('user123', 1);
    }

    public function test_get_tier_name_returns_correct_names()
    {
        // Arrange & Act & Assert
        $reflection = new \ReflectionClass($this->subscriptionService);
        $method = $reflection->getMethod('getTierName');
        $method->setAccessible(true);

        $this->assertEquals('Free Tier', $method->invoke($this->subscriptionService, 0));
        $this->assertEquals('Enthusiast', $method->invoke($this->subscriptionService, 1));
        $this->assertEquals('Professional', $method->invoke($this->subscriptionService, 2));
        $this->assertEquals('Unknown Tier', $method->invoke($this->subscriptionService, 999));
    }

    public function test_get_tier_description_returns_correct_descriptions()
    {
        // Arrange & Act & Assert
        $reflection = new \ReflectionClass($this->subscriptionService);
        $method = $reflection->getMethod('getTierDescription');
        $method->setAccessible(true);

        $this->assertEquals('Basic recipe management for casual cooks', $method->invoke($this->subscriptionService, 0));
        $this->assertEquals('Enhanced features for enthusiast cooks', $method->invoke($this->subscriptionService, 1));
        $this->assertEquals('Professional features for serious chefs', $method->invoke($this->subscriptionService, 2));
        $this->assertEquals('Unknown tier description', $method->invoke($this->subscriptionService, 999));
    }

    public function test_get_tier_features_returns_correct_feature_arrays()
    {
        // Arrange & Act
        $reflection = new \ReflectionClass($this->subscriptionService);
        $method = $reflection->getMethod('getTierFeatures');
        $method->setAccessible(true);

        $freeTierFeatures = $method->invoke($this->subscriptionService, 0);
        $tier1Features = $method->invoke($this->subscriptionService, 1);
        $tier2Features = $method->invoke($this->subscriptionService, 2);
        $unknownFeatures = $method->invoke($this->subscriptionService, 999);

        // Assert
        $this->assertIsArray($freeTierFeatures);
        $this->assertContains('Create up to 25 recipes (all recipes are publicly viewable)', $freeTierFeatures);
        $this->assertContains('Create 1 cookbook (publicly viewable)', $freeTierFeatures);

        $this->assertIsArray($tier1Features);
        $this->assertContains('Unlimited recipes (all recipes are publicly viewable)', $tier1Features);
        $this->assertContains('Create up to 10 cookbooks (publicly viewable)', $tier1Features);

        $this->assertIsArray($tier2Features);
        $this->assertContains('All Tier 1 features', $tier2Features);
        $this->assertContains('Privacy controls (ability to make recipes and cookbooks private or public)', $tier2Features);

        $this->assertEquals(['Unknown tier features'], $unknownFeatures);
    }

    public function test_get_tier_price_id_throws_exception_for_invalid_tier()
    {
        // Arrange & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid subscription tier');

        // Act
        $reflection = new \ReflectionClass($this->subscriptionService);
        $method = $reflection->getMethod('getTierPriceId');
        $method->setAccessible(true);
        $method->invoke($this->subscriptionService, 0);
    }

    public function test_update_user_subscription_handles_stripe_cancellation_failure_gracefully()
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->stripe_subscription_id = 'sub_test123';
        $user->shouldReceive('save')->once();

        User::shouldReceive('find')
            ->once()
            ->with('user123')
            ->andReturn($user);

        // Mock Stripe subscriptions service to throw exception
        $stripeSubscriptions = Mockery::mock();
        $stripeSubscriptions->shouldReceive('cancel')
            ->once()
            ->with('sub_test123')
            ->andThrow(new \Exception('Stripe error'));

        $this->stripeClient->subscriptions = $stripeSubscriptions;

        // Mock Log facade
        \Log::shouldReceive('error')
            ->once()
            ->with('Failed to cancel Stripe subscription: Stripe error');

        // Mock getUserSubscription call at the end
        $this->subscriptionService = Mockery::mock(SubscriptionService::class)->makePartial();
        $this->subscriptionService->shouldReceive('getUserSubscription')
            ->once()
            ->with('user123')
            ->andReturn(['test' => 'data']);

        // Replace stripe in the partial mock
        $reflection = new \ReflectionClass($this->subscriptionService);
        $stripeProperty = $reflection->getProperty('stripe');
        $stripeProperty->setAccessible(true);
        $stripeProperty->setValue($this->subscriptionService, $this->stripeClient);

        // Act
        $result = $this->subscriptionService->updateUserSubscription('user123', 0);

        // Assert - Should continue despite Stripe error
        $this->assertEquals(0, $user->subscription_tier);
        $this->assertEquals('active', $user->subscription_status);
        $this->assertNull($user->stripe_subscription_id);
        $this->assertEquals(['test' => 'data'], $result);
    }
}
