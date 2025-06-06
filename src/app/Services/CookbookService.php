<?php

namespace App\Services;

use App\Models\Cookbook;
use App\Models\Recipe;
use App\Repositories\Interfaces\CookbookRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class CookbookService
{
    protected $cookbookRepository;

    /**
     * Create a new service instance.
     *
     * @param  \App\Repositories\Interfaces\CookbookRepositoryInterface  $cookbookRepository
     * @return void
     */
    public function __construct(CookbookRepositoryInterface $cookbookRepository)
    {
        $this->cookbookRepository = $cookbookRepository;
    }

    /**
     * Get all cookbooks for a user.
     *
     * @param  string  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCookbooks($userId)
    {
        return $this->cookbookRepository->getUserCookbooks($userId);
    }

    /**
     * Get all public cookbooks.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicCookbooks()
    {
        return $this->cookbookRepository->getPublicCookbooks();
    }

    /**
     * Get a specific cookbook.
     *
     * @param  string  $id
     * @return \App\Models\Cookbook|null
     */
    public function getCookbook($id)
    {
        return $this->cookbookRepository->find($id);
    }

    /**
     * Get a cookbook with its recipes.
     *
     * @param  string  $id
     * @return \App\Models\Cookbook|null
     */
    public function getCookbookWithRecipes($id)
    {
        $cookbook = $this->getCookbook($id);
        
        if (!$cookbook) {
            return null;
        }
        
        $recipeIds = array_column($cookbook->recipe_ids ?? [], 'recipe_id');
        $recipes = Recipe::whereIn('_id', $recipeIds)->get();
        
        // Sort recipes by order
        $orderedRecipes = [];
        foreach ($cookbook->recipe_ids ?? [] as $item) {
            foreach ($recipes as $recipe) {
                if ($recipe->_id == $item['recipe_id']) {
                    $recipe->order = $item['order'];
                    $orderedRecipes[] = $recipe;
                    break;
                }
            }
        }
        
        usort($orderedRecipes, function($a, $b) {
            return $a->order <=> $b->order;
        });
        
        $cookbook->recipes = $orderedRecipes;
        
        return $cookbook;
    }

    /**
     * Create a new cookbook.
     *
     * @param  string  $userId
     * @param  array  $data
     * @return \App\Models\Cookbook
     */
    public function createCookbook($userId, array $data)
    {
        $data['user_id'] = $userId;
        
        return $this->cookbookRepository->create($data);
    }

    /**
     * Update an existing cookbook.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \App\Models\Cookbook
     */
    public function updateCookbook($id, array $data)
    {
        return $this->cookbookRepository->update($id, $data);
    }

    /**
     * Delete a cookbook.
     *
     * @param  string  $id
     * @return bool
     */
    public function deleteCookbook($id)
    {
        return $this->cookbookRepository->delete($id);
    }

    /**
     * Toggle cookbook privacy.
     *
     * @param  string  $id
     * @return \App\Models\Cookbook
     */
    public function toggleCookbookPrivacy($id)
    {
        $cookbook = $this->getCookbook($id);
        
        if (!$cookbook) {
            return null;
        }
        
        $cookbook->is_private = !$cookbook->is_private;
        $cookbook->save();
        
        return $cookbook;
    }

    /**
     * Add recipes to a cookbook.
     *
     * @param  string  $id
     * @param  array  $recipeIds
     * @return \App\Models\Cookbook
     */
    public function addRecipesToCookbook($id, array $recipeIds)
    {
        $cookbook = $this->getCookbook($id);
        
        if (!$cookbook) {
            return null;
        }
        
        // Get existing recipe_ids
        $existingRecipeIds = $cookbook->recipe_ids ?? [];
        $existingIds = array_column($existingRecipeIds, 'recipe_id');
        
        // Get next order number
        $nextOrder = count($existingRecipeIds);
        
        // Add new recipes
        foreach ($recipeIds as $recipeId) {
            // Skip if already in cookbook
            if (in_array($recipeId, $existingIds)) {
                continue;
            }
            
            $existingRecipeIds[] = [
                'recipe_id' => $recipeId,
                'order' => $nextOrder++
            ];
        }
        
        $cookbook->recipe_ids = $existingRecipeIds;
        $cookbook->save();
        
        return $cookbook;
    }

    /**
     * Remove a recipe from a cookbook.
     *
     * @param  string  $id
     * @param  string  $recipeId
     * @return \App\Models\Cookbook
     */
    public function removeRecipeFromCookbook($id, $recipeId)
    {
        $cookbook = $this->getCookbook($id);
        
        if (!$cookbook) {
            return null;
        }
        
        // Filter out the recipe to remove
        $recipeIds = array_filter($cookbook->recipe_ids ?? [], function($item) use ($recipeId) {
            return $item['recipe_id'] != $recipeId;
        });
        
        // Reindex array
        $cookbook->recipe_ids = array_values($recipeIds);
        $cookbook->save();
        
        return $cookbook;
    }

    /**
     * Reorder recipes in a cookbook.
     *
     * @param  string  $id
     * @param  array  $recipeOrder
     * @return \App\Models\Cookbook
     */
    public function reorderCookbookRecipes($id, array $recipeOrder)
    {
        $cookbook = $this->getCookbook($id);
        
        if (!$cookbook) {
            return null;
        }
        
        // Update orders based on the provided array
        $recipeIds = $cookbook->recipe_ids ?? [];
        
        foreach ($recipeIds as &$item) {
            if (isset($recipeOrder[$item['recipe_id']])) {
                $item['order'] = $recipeOrder[$item['recipe_id']];
            }
        }
        
        // Sort by order
        usort($recipeIds, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        
        $cookbook->recipe_ids = $recipeIds;
        $cookbook->save();
        
        return $cookbook;
    }

    /**
     * Check if a user can access a cookbook.
     *
     * @param  string  $userId
     * @param  \App\Models\Cookbook  $cookbook
     * @return bool
     */
    public function userCanAccessCookbook($userId, $cookbook)
    {
        // Public cookbooks are accessible to everyone
        if (empty($cookbook->is_private)) {
            return true;
        }
        
        // Owner can access their own cookbooks
        if ($cookbook->user_id == $userId) {
            return true;
        }
        
        // Admins can access all cookbooks
        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            return true;
        }
        
        return false;
    }

    /**
     * Generate cookbook text for export.
     *
     * @param  \App\Models\Cookbook  $cookbook
     * @return string
     */
    public function generateCookbookText($cookbook)
    {
        // Get cookbook with recipes
        $cookbook = $this->getCookbookWithRecipes($cookbook->_id);
        
        if (!$cookbook) {
            return '';
        }
        
        $text = "COOKBOOK: {$cookbook->name}\n\n";
        
        if ($cookbook->description) {
            $text .= "DESCRIPTION:\n";
            $text .= $cookbook->description . "\n\n";
        }
        
        // Table of contents
        $text .= "TABLE OF CONTENTS:\n";
        $page = 1;
        
        foreach ($cookbook->recipes as $index => $recipe) {
            $text .= ($index + 1) . ". {$recipe->name} - Page " . $page . "\n";
            $page += 2; // Assuming each recipe takes 2 pages
        }
        
        $text .= "\n\n";
        
        // Recipes
        foreach ($cookbook->recipes as $index => $recipe) {
            $text .= str_repeat("-", 80) . "\n";
            $text .= "RECIPE " . ($index + 1) . ": {$recipe->name}\n";
            $text .= str_repeat("-", 80) . "\n\n";
            
            if ($recipe->source && $recipe->source->name) {
                $text .= "Source: {$recipe->source->name}\n";
            }
            
            if ($recipe->classification && $recipe->classification->name) {
                $text .= "Classification: {$recipe->classification->name}\n";
            }
            
            if ($recipe->servings) {
                $text .= "Servings: {$recipe->servings}\n";
            }
            
            $text .= "\nINGREDIENTS:\n";
            $text .= $recipe->ingredients . "\n\n";
            
            $text .= "INSTRUCTIONS:\n";
            $text .= $recipe->instructions . "\n\n";
            
            if ($recipe->notes) {
                $text .= "NOTES:\n";
                $text .= $recipe->notes . "\n\n";
            }
            
            if ($recipe->calories || $recipe->fat || $recipe->cholesterol || $recipe->sodium || $recipe->protein) {
                $text .= "NUTRITIONAL INFORMATION:\n";
                
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
            }
            
            $text .= "\n\n";
        }
        
        return $text;
    }
}
