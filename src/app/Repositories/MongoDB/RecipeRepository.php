<?php

namespace App\Repositories\MongoDB;

use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;

class RecipeRepository implements RecipeRepositoryInterface
{
    /**
     * Get all recipes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Recipe::all();
    }

    /**
     * Get a recipe by ID.
     *
     * @param  string  $id
     * @return \App\Models\Recipe|null
     */
    public function find($id)
    {
        return Recipe::find($id);
    }

    /**
     * Create a new recipe.
     *
     * @return \App\Models\Recipe
     */
    public function create(array $data)
    {
        return Recipe::create($data);
    }

    /**
     * Update a recipe.
     *
     * @param  string  $id
     * @return \App\Models\Recipe
     */
    public function update($id, array $data)
    {
        $recipe = $this->find($id);

        if (! $recipe) {
            return null;
        }

        $recipe->update($data);

        return $recipe;
    }

    /**
     * Delete a recipe.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete($id)
    {
        $recipe = $this->find($id);

        if (! $recipe) {
            return false;
        }

        return $recipe->delete();
    }

    /**
     * Get recipes for a specific user.
     *
     * @param  string  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserRecipes($userId)
    {
        return Recipe::where('user_id', $userId)->get();
    }

    /**
     * Get public recipes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicRecipes()
    {
        return Recipe::where('is_private', false)
            ->orWhereNull('is_private')
            ->get();
    }

    /**
     * Search for recipes.
     *
     * @param  string  $query
     * @param  string|null  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchRecipes($query, $userId = null)
    {
        // Build base query
        $baseQuery = Recipe::search($query);

        // Apply visibility restrictions
        if ($userId) {
            // User can see their own recipes (private or public)
            // Plus any public recipes from other users
            $baseQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere(function ($q2) {
                        $q2->where('is_private', false)
                            ->orWhereNull('is_private');
                    });
            });
        } else {
            // Guest can only see public recipes
            $baseQuery->where(function ($q) {
                $q->where('is_private', false)
                    ->orWhereNull('is_private');
            });
        }

        // Get results
        return $baseQuery->get();
    }
}
