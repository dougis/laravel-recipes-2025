<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MongoDB doesn't require traditional schema migrations, but we can
        // use this file to create indexes for better performance
        
        // Create text index for recipes
        Schema::connection('mongodb')->table('recipes', function (Blueprint $collection) {
            $collection->index('user_id');
            $collection->index('name');
            $collection->index('is_private');
            $collection->index(['name' => 'text', 'ingredients' => 'text', 'tags' => 'text', 'instructions' => 'text'], 'recipe_search_index');
        });
        
        // Create indexes for cookbooks
        Schema::connection('mongodb')->table('cookbooks', function (Blueprint $collection) {
            $collection->index('user_id');
            $collection->index('name');
            $collection->index('is_private');
        });
        
        // Create indexes for users
        Schema::connection('mongodb')->table('users', function (Blueprint $collection) {
            $collection->unique('email');
            $collection->index('subscription_tier');
        });
        
        // Create indexes for metadata collections
        Schema::connection('mongodb')->table('classifications', function (Blueprint $collection) {
            $collection->index('name');
        });
        
        Schema::connection('mongodb')->table('sources', function (Blueprint $collection) {
            $collection->index('name');
        });
        
        Schema::connection('mongodb')->table('meals', function (Blueprint $collection) {
            $collection->index('name');
        });
        
        Schema::connection('mongodb')->table('courses', function (Blueprint $collection) {
            $collection->index('name');
        });
        
        Schema::connection('mongodb')->table('preparations', function (Blueprint $collection) {
            $collection->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::connection('mongodb')->table('recipes', function (Blueprint $collection) {
            $collection->dropIndex('user_id');
            $collection->dropIndex('name');
            $collection->dropIndex('is_private');
            $collection->dropIndex('recipe_search_index');
        });
        
        Schema::connection('mongodb')->table('cookbooks', function (Blueprint $collection) {
            $collection->dropIndex('user_id');
            $collection->dropIndex('name');
            $collection->dropIndex('is_private');
        });
        
        Schema::connection('mongodb')->table('users', function (Blueprint $collection) {
            $collection->dropIndex('email');
            $collection->dropIndex('subscription_tier');
        });
        
        Schema::connection('mongodb')->table('classifications', function (Blueprint $collection) {
            $collection->dropIndex('name');
        });
        
        Schema::connection('mongodb')->table('sources', function (Blueprint $collection) {
            $collection->dropIndex('name');
        });
        
        Schema::connection('mongodb')->table('meals', function (Blueprint $collection) {
            $collection->dropIndex('name');
        });
        
        Schema::connection('mongodb')->table('courses', function (Blueprint $collection) {
            $collection->dropIndex('name');
        });
        
        Schema::connection('mongodb')->table('preparations', function (Blueprint $collection) {
            $collection->dropIndex('name');
        });
    }
};
