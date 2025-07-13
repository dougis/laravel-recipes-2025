<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'password' => 'hashed',
        'subscription_tier' => 'integer',
        'admin_override' => 'boolean',
    ];

    /**
     * Check if user is admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        // Add admin check logic here
        // For now, assume subscription_tier = 100 is admin
        return $this->subscription_tier === 100;
    }

    /**
     * Check if user has Tier 2 access.
     *
     * @return bool
     */
    public function hasTier2Access()
    {
        return $this->subscription_tier >= 2 || $this->admin_override;
    }

    /**
     * Check if user has Tier 1 access.
     *
     * @return bool
     */
    public function hasTier1Access()
    {
        return $this->subscription_tier >= 1 || $this->admin_override;
    }

    /**
     * Get the recipes owned by the user.
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Get the cookbooks owned by the user.
     */
    public function cookbooks()
    {
        return $this->hasMany(Cookbook::class);
    }

    /**
     * Get recipe count for user.
     *
     * @return int
     */
    public function recipeCount()
    {
        return $this->recipes()->count();
    }

    /**
     * Get cookbook count for user.
     *
     * @return int
     */
    public function cookbookCount()
    {
        return $this->cookbooks()->count();
    }

    /**
     * Check if user can create more recipes.
     *
     * @return bool
     */
    public function canCreateRecipe()
    {
        // Free tier users are limited to 25 recipes
        if ($this->subscription_tier === 0 && !$this->admin_override) {
            return $this->recipeCount() < 25;
        }

        // Paid tiers and admin override have unlimited recipes
        return true;
    }

    /**
     * Check if user can create more cookbooks.
     *
     * @return bool
     */
    public function canCreateCookbook()
    {
        // Free tier users are limited to 1 cookbook
        if ($this->subscription_tier === 0 && !$this->admin_override) {
            return $this->cookbookCount() < 1;
        }

        // Tier 1 users are limited to 10 cookbooks
        if ($this->subscription_tier === 1 && !$this->admin_override) {
            return $this->cookbookCount() < 10;
        }

        // Tier 2 and admin override have unlimited cookbooks
        return true;
    }
}
