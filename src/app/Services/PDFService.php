<?php

namespace App\Services;

use App\Models\Cookbook;
use App\Models\Recipe;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{
    /**
     * Generate a PDF for a recipe.
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateRecipePDF(Recipe $recipe)
    {
        // Load relationships
        $recipe->load(['source', 'classification', 'meals', 'courses', 'preparations']);

        $pdf = PDF::loadView('pdfs.recipe', compact('recipe'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Generate a PDF for a cookbook.
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateCookbookPDF(Cookbook $cookbook)
    {
        // Get recipes in the cookbook
        $recipeIds = array_column($cookbook->recipe_ids ?? [], 'recipe_id');
        $recipes = Recipe::whereIn('_id', $recipeIds)
            ->with(['source', 'classification', 'meals', 'courses', 'preparations'])
            ->get();

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

        usort($orderedRecipes, function ($a, $b) {
            return $a->order <=> $b->order;
        });

        $cookbook->recipes = $orderedRecipes;

        $pdf = PDF::loadView('pdfs.cookbook', compact('cookbook'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}
