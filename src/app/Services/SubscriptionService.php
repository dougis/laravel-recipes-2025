<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Stripe\StripeClient;

class SubscriptionService
{
    protected $stripe;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Get user subscription details.
     *
     * @param  string  $userId
     * @return array
     */
    public function getUserSubscription($userId)
    {
        $user = User::find($userId);

        if (! $user) {
            return null;
        }

        $subscription = Subscription::where('tier', $user->subscription_tier)->first();

        if (! $subscription) {
            // Create default subscription data if not found
            $subscription = [
                'name' => $this->getTierName($user->subscription_tier),
                'description' => $this->getTierDescription($user->subscription_tier),
                'tier' => $user->subscription_tier,
                'features' => $this->getTierFeatures($user->subscription_tier),
            ];
        }

        return [
            'user' => [
                'subscription_tier' => $user->subscription_tier,
                'subscription_status' => $user->subscription_status,
                'subscription_expires_at' => $user->subscription_expires_at,
                'admin_override' => $user->admin_override,
            ],
            'subscription' => $subscription,
        ];
    }

    /**
     * Update user subscription.
     *
     * @param  string  $userId
     * @param  int  $tier
     * @param  string|null  $paymentMethodId
     * @return array
     */
    public function updateUserSubscription($userId, $tier, $paymentMethodId = null)
    {
        $user = User::find($userId);

        if (! $user) {
            throw new \Exception('User not found');
        }

        // Handle downgrade to free tier
        if ($tier === 0) {
            // Cancel Stripe subscription if exists
            if ($user->stripe_subscription_id) {
                try {
                    $this->stripe->subscriptions->cancel($user->stripe_subscription_id);
                } catch (\Exception $e) {
                    // Log error but continue with downgrade
                    \Log::error('Failed to cancel Stripe subscription: '.$e->getMessage());
                }
            }

            $user->subscription_tier = 0;
            $user->subscription_status = 'active';
            $user->stripe_subscription_id = null;
            $user->save();

            return $this->getUserSubscription($userId);
        }

        // Handle upgrade to paid tier
        if (! $paymentMethodId) {
            throw new \Exception('Payment method ID is required for paid subscriptions');
        }

        // Get price ID for the tier
        $priceId = $this->getTierPriceId($tier);

        // Create or update Stripe customer
        if (! $user->stripe_customer_id) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'payment_method' => $paymentMethodId,
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ]);

            $user->stripe_customer_id = $customer->id;
        } else {
            // Update default payment method
            $this->stripe->customers->update($user->stripe_customer_id, [
                'payment_method' => $paymentMethodId,
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ]);
        }

        // Create or update subscription
        if (! $user->stripe_subscription_id) {
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $user->stripe_customer_id,
                'items' => [['price' => $priceId]],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $user->stripe_subscription_id = $subscription->id;
        } else {
            // Update existing subscription
            $subscription = $this->stripe->subscriptions->retrieve($user->stripe_subscription_id);

            if ($subscription->status === 'active') {
                // Update subscription items
                $this->stripe->subscriptions->update($user->stripe_subscription_id, [
                    'items' => [
                        [
                            'id' => $subscription->items->data[0]->id,
                            'price' => $priceId,
                        ],
                    ],
                ]);
            } else {
                // Create new subscription if previous was canceled
                $subscription = $this->stripe->subscriptions->create([
                    'customer' => $user->stripe_customer_id,
                    'items' => [['price' => $priceId]],
                    'expand' => ['latest_invoice.payment_intent'],
                ]);

                $user->stripe_subscription_id = $subscription->id;
            }
        }

        // Update user subscription details
        $user->subscription_tier = $tier;
        $user->subscription_status = $subscription->status;
        $user->subscription_expires_at = date('Y-m-d H:i:s', $subscription->current_period_end);
        $user->save();

        return $this->getUserSubscription($userId);
    }

    /**
     * Get the name for a subscription tier.
     *
     * @param  int  $tier
     * @return string
     */
    protected function getTierName($tier)
    {
        switch ($tier) {
            case 0:
                return 'Free Tier';
            case 1:
                return 'Enthusiast';
            case 2:
                return 'Professional';
            default:
                return 'Unknown Tier';
        }
    }

    /**
     * Get the description for a subscription tier.
     *
     * @param  int  $tier
     * @return string
     */
    protected function getTierDescription($tier)
    {
        switch ($tier) {
            case 0:
                return 'Basic recipe management for casual cooks';
            case 1:
                return 'Enhanced features for enthusiast cooks';
            case 2:
                return 'Professional features for serious chefs';
            default:
                return 'Unknown tier description';
        }
    }

    /**
     * Get the features for a subscription tier.
     *
     * @param  int  $tier
     * @return array
     */
    protected function getTierFeatures($tier)
    {
        switch ($tier) {
            case 0:
                return [
                    'Create up to 25 recipes (all recipes are publicly viewable)',
                    'Basic recipe details (ingredients, instructions)',
                    'Create 1 cookbook (publicly viewable)',
                    'Print individual recipes',
                    'Basic search functionality',
                ];
            case 1:
                return [
                    'Unlimited recipes (all recipes are publicly viewable)',
                    'Enhanced recipe details (nutritional info, notes)',
                    'Create up to 10 cookbooks (publicly viewable)',
                    'Advanced search and filtering',
                    'Print cookbooks with table of contents',
                    'Export recipes in multiple formats',
                ];
            case 2:
                return [
                    'All Tier 1 features',
                    'Advanced recipe categorization',
                    'Unlimited cookbooks',
                    'Custom cookbook templates',
                    'Recipe scaling functionality',
                    'Meal planning features',
                    'Inventory management',
                    'Privacy controls (ability to make recipes and cookbooks private or public)',
                ];
            default:
                return ['Unknown tier features'];
        }
    }

    /**
     * Get the Stripe price ID for a subscription tier.
     *
     * @param  int  $tier
     * @return string
     */
    protected function getTierPriceId($tier)
    {
        switch ($tier) {
            case 1:
                return config('services.stripe.prices.tier1');
            case 2:
                return config('services.stripe.prices.tier2');
            default:
                throw new \Exception('Invalid subscription tier');
        }
    }
}
