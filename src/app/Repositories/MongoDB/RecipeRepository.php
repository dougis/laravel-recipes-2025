<?php

namespace App\Repositories\MongoDB;

use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;

class RecipeRepository implements RecipeRepositoryInterface
{
    protected $model;

    public function __construct(Recipe $model)
    {
        $this->model = $model;
    }
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
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getUserRecipes($userId, $page = 1, $limit = 10)
    {
        $query = $this->model->where('user_id', $userId);

        $total = $query->count();
        $recipes = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'recipes' => $recipes,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $limit,
                'total' => $total,
                'last_page' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * Get public recipes.
     *
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getPublicRecipes($page = 1, $limit = 10)
    {
        $query = Recipe::where(function ($q) {
            $q->where('is_private', false)
                ->orWhereNull('is_private');
        });

        $total = $query->count();
        $recipes = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'recipes' => $recipes,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $limit,
                'total' => $total,
                'last_page' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * Search for recipes.
     *
     * @param  string  $query
     * @param  string|null  $userId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function searchRecipes($query, $userId = null, $page = 1, $limit = 10)
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

        // Get total count
        $total = $baseQuery->count();

        // Get paginated results
        $recipes = $baseQuery->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        return [
            'recipes' => $recipes,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $limit,
                'total' => $total,
                'last_page' => ceil($total / $limit),
            ],
        ];
    }
}
