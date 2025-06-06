<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'subscription_tier' => 100, // Admin tier
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYears(10),
            'admin_override' => true,
        ]);

        // Create free tier user
        User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'subscription_tier' => 0, // Free tier
            'subscription_status' => 'active',
        ]);

        // Create tier 1 user
        User::create([
            'name' => 'Tier 1 User',
            'email' => 'tier1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'subscription_tier' => 1, // Tier 1
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYear(),
            'stripe_customer_id' => 'cus_example1',
            'stripe_subscription_id' => 'sub_example1',
        ]);

        // Create tier 2 user
        User::create([
            'name' => 'Tier 2 User',
            'email' => 'tier2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'subscription_tier' => 2, // Tier 2
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addYear(),
            'stripe_customer_id' => 'cus_example2',
            'stripe_subscription_id' => 'sub_example2',
        ]);
    }
}
