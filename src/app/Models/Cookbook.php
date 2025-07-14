<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Cookbook extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'cookbooks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image',
        'recipe_ids',
        'is_private',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recipe_ids' => 'array',
        'is_private' => 'boolean',
    ];

    /**
     * Get the user that owns the cookbook.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipes in this cookbook.
     */
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class);
    }

    /**
     * Add a recipe to the cookbook.
     *
     * @param  string  $recipeId
     * @param  int  $order
     * @return void
     */
    public function addRecipe($recipeId, $order = null)
    {
        // Get current recipe_ids
        $recipeIds = $this->recipe_ids ?? [];

        // If order is not specified, add to the end
        if ($order === null) {
            $order = count($recipeIds);
        }

        // Add new recipe with order
        $recipeIds[] = [
            'recipe_id' => $recipeId,
            'order' => $order,
        ];

        // Update the model
        $this->recipe_ids = $recipeIds;
    }

    /**
     * Remove a recipe from the cookbook.
     *
     * @param  string  $recipeId
     * @return void
     */
    public function removeRecipe($recipeId)
    {
        // Get current recipe_ids
        $recipeIds = $this->recipe_ids ?? [];

        // Filter out the recipe to remove
        $recipeIds = array_filter($recipeIds, function ($item) use ($recipeId) {
            return $item['recipe_id'] != $recipeId;
        });

        // Reindex array
        $recipeIds = array_values($recipeIds);

        // Update the model
        $this->recipe_ids = $recipeIds;
    }

    /**
     * Reorder recipes in the cookbook.
     *
     * @param  array  $recipeOrder  Array of recipe_id => order pairs
     * @return void
     */
    public function reorderRecipes($recipeOrder)
    {
        // Get current recipe_ids
        $recipeIds = $this->recipe_ids ?? [];

        // Update orders based on the provided array
        foreach ($recipeIds as &$item) {
            if (isset($recipeOrder[$item['recipe_id']])) {
                $item['order'] = $recipeOrder[$item['recipe_id']];
            }
        }

        // Sort by order
        usort($recipeIds, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Update the model
        $this->recipe_ids = $recipeIds;
    }
}
