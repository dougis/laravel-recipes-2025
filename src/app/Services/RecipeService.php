<?php

namespace App\Services;

use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class RecipeService
{
    protected $recipeRepository;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(RecipeRepositoryInterface $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Get all recipes for a user.
     *
     * @param  string  $userId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getUserRecipes($userId, $page = 1, $limit = 10)
    {
        return $this->recipeRepository->getUserRecipes($userId, $page, $limit);
    }

    /**
     * Get all public recipes.
     *
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getPublicRecipes($page = 1, $limit = 10)
    {
        return $this->recipeRepository->getPublicRecipes($page, $limit);
    }

    /**
     * Get a specific recipe.
     *
     * @param  string  $id
     * @return \App\Models\Recipe|null
     */
    public function getRecipe($id)
    {
        return $this->recipeRepository->find($id);
    }

    /**
     * Create a new recipe.
     *
     * @param  string  $userId
     * @return \App\Models\Recipe
     */
    public function createRecipe($userId, array $data)
    {
        $data['user_id'] = $userId;

        return $this->recipeRepository->create($data);
    }

    /**
     * Update an existing recipe.
     *
     * @param  string  $id
     * @return \App\Models\Recipe
     */
    public function updateRecipe($id, array $data)
    {
        return $this->recipeRepository->update($id, $data);
    }

    /**
     * Delete a recipe.
     *
     * @param  string  $id
     * @return bool
     */
    public function deleteRecipe($id)
    {
        return $this->recipeRepository->delete($id);
    }

    /**
     * Toggle recipe privacy.
     *
     * @param  string  $id
     * @return \App\Models\Recipe
     */
    public function toggleRecipePrivacy($id)
    {
        $recipe = $this->getRecipe($id);

        if (! $recipe) {
            return null;
        }

        $recipe->is_private = ! $recipe->is_private;
        $recipe->save();

        return $recipe;
    }

    /**
     * Search for recipes.
     *
     * @param  string  $query
     * @param  string|null  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchRecipes($query, $userId = null, $page = 1, $limit = 10)
    {
        return $this->recipeRepository->searchRecipes($query, $userId, $page, $limit);
    }

    /**
     * Check if a user can access a recipe.
     *
     * @param  string  $userId
     * @param  \App\Models\Recipe  $recipe
     * @return bool
     */
    public function userCanAccessRecipe($userId, $recipe)
    {
        // Public recipes are accessible to everyone
        if (empty($recipe->is_private)) {
            return true;
        }

        // Owner can access their own recipes
        if ($recipe->user_id == $userId) {
            return true;
        }

        // Admins can access all recipes
        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Generate recipe text for export.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return string
     */
    public function generateRecipeText($recipe)
    {
        $text = "RECIPE: {$recipe->name}\n\n";
        $text .= $this->generateRecipeMetadata($recipe);
        $text .= $this->generateRecipeContent($recipe);
        $text .= $this->generateRecipeNutrition($recipe);

        return $text;
    }

    private function generateRecipeMetadata($recipe)
    {
        $text = '';

        if ($recipe->source && $recipe->source->name) {
            $text .= "Source: {$recipe->source->name}\n";
        }

        if ($recipe->classification && $recipe->classification->name) {
            $text .= "Classification: {$recipe->classification->name}\n";
        }

        if ($recipe->servings) {
            $text .= "Servings: {$recipe->servings}\n";
        }

        return $text;
    }

    private function generateRecipeContent($recipe)
    {
        $text = "\nINGREDIENTS:\n";
        $text .= $recipe->ingredients."\n\n";

        $text .= "INSTRUCTIONS:\n";
        $text .= $recipe->instructions."\n\n";

        if ($recipe->notes) {
            $text .= "NOTES:\n";
            $text .= $recipe->notes."\n\n";
        }

        return $text;
    }

    private function generateRecipeNutrition($recipe)
    {
        if (! $this->hasNutritionalData($recipe)) {
            return '';
        }

        $text = "NUTRITIONAL INFORMATION:\n";

        if ($recipe->calories) {
            $text .= "Calories: {$recipe->calories}\n";
        }

        if ($recipe->fat) {
            $text .= "Fat: {$recipe->fat}g\n";
        }

        if ($recipe->cholesterol) {
            $text .= "Cholesterol: {$recipe->cholesterol}mg\n";
        }

        if ($recipe->sodium) {
            $text .= "Sodium: {$recipe->sodium}mg\n";
        }

        if ($recipe->protein) {
            $text .= "Protein: {$recipe->protein}g\n";
        }

        return $text;
    }

    private function hasNutritionalData($recipe)
    {
        return $recipe->calories || $recipe->fat || $recipe->cholesterol || $recipe->sodium || $recipe->protein;
    }
}
