<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Free tier
        Subscription::create([
            'name' => 'Free Tier',
            'description' => 'Basic recipe management for casual cooks',
            'price' => 0,
            'tier' => 0,
            'features' => [
                'Create up to 25 recipes (all recipes are publicly viewable)',
                'Basic recipe details (ingredients, instructions)',
                'Create 1 cookbook (publicly viewable)',
                'Print individual recipes',
                'Basic search functionality',
            ],
        ]);

        // Paid tier 1
        Subscription::create([
            'name' => 'Enthusiast',
            'description' => 'Enhanced features for enthusiast cooks',
            'price' => 9.99,
            'tier' => 1,
            'features' => [
                'Unlimited recipes (all recipes are publicly viewable)',
                'Enhanced recipe details (nutritional info, notes)',
                'Create up to 10 cookbooks (publicly viewable)',
                'Advanced search and filtering',
                'Print cookbooks with table of contents',
                'Export recipes in multiple formats',
            ],
        ]);

        // Paid tier 2
        Subscription::create([
            'name' => 'Professional',
            'description' => 'Professional features for serious chefs',
            'price' => 19.99,
            'tier' => 2,
            'features' => [
                'All Tier 1 features',
                'Advanced recipe categorization',
                'Unlimited cookbooks',
                'Custom cookbook templates',
                'Recipe scaling functionality',
                'Meal planning features',
                'Inventory management',
                'Privacy controls (ability to make recipes and cookbooks private or public)',
            ],
        ]);

        // Admin tier (not shown to regular users)
        Subscription::create([
            'name' => 'Administrator',
            'description' => 'Full system access for administrators',
            'price' => 0,
            'tier' => 100,
            'features' => [
                'All features of all tiers',
                'User management',
                'System administration',
                'Subscription management',
            ],
        ]);
    }
}
