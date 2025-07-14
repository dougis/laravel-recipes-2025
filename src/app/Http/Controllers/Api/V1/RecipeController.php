<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecipeRequest;
use App\Models\Recipe;
use App\Services\PDFService;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    protected $recipeService;

    protected $pdfService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(RecipeService $recipeService, PDFService $pdfService)
    {
        $this->recipeService = $recipeService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of the user's recipes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $result = $this->recipeService->getUserRecipes($user->id, $page, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Display a listing of public recipes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicRecipes(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $result = $this->recipeService->getPublicRecipes($page, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Store a newly created recipe in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RecipeRequest $request)
    {
        $user = Auth::user();

        // Check if user can create more recipes
        if (! $user->canCreateRecipe()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe limit reached for your subscription tier',
            ], 403);
        }

        $recipe = $this->recipeService->createRecipe($user->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe created successfully',
            'data' => $recipe,
        ], 201);
    }

    /**
     * Display the specified recipe.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $recipe = $this->recipeService->getRecipe($id);

        // Check if recipe exists
        if (! $recipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe not found',
            ], 404);
        }

        // Check if user has access to this recipe
        if (! $this->recipeService->userCanAccessRecipe(Auth::id(), $recipe)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this recipe',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $recipe,
        ]);
    }

    /**
     * Update the specified recipe in storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(RecipeRequest $request, $id)
    {
        $recipe = Recipe::find($id);

        // Check if recipe exists
        if (! $recipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe not found',
            ], 404);
        }

        // Check if user owns this recipe
        if ($recipe->user_id != Auth::id() && ! Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this recipe',
            ], 403);
        }

        $recipe = $this->recipeService->updateRecipe($id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe updated successfully',
            'data' => $recipe,
        ]);
    }

    /**
     * Remove the specified recipe from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $recipe = Recipe::find($id);

        // Check if recipe exists
        if (! $recipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe not found',
            ], 404);
        }

        // Check if user owns this recipe
        if ($recipe->user_id != Auth::id() && ! Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this recipe',
            ], 403);
        }

        $this->recipeService->deleteRecipe($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe deleted successfully',
        ]);
    }

    /**
     * Toggle recipe privacy.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function togglePrivacy($id)
    {
        $recipe = Recipe::find($id);
        $user = Auth::user();

        // Check if recipe exists
        if (! $recipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe not found',
            ], 404);
        }

        // Check if user owns this recipe
        if ($recipe->user_id != $user->id && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this recipe',
            ], 403);
        }

        // Check if user has Tier 2 access for privacy control
        if (! $user->hasTier2Access() && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Privacy control is only available for Tier 2 subscribers',
            ], 403);
        }

        $recipe = $this->recipeService->toggleRecipePrivacy($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe privacy updated successfully',
            'data' => $recipe,
        ]);
    }

    /**
     * Search for recipes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchRecipes(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $user = $request->user();
        $userId = $user ? $user->id : null;
        $result = $this->recipeService->searchRecipes($request->query('query'), $userId, $page, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Get printable version of a recipe.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function getPrintableRecipe($id)
    {
        try {
            $recipe = Recipe::find($id);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid recipe ID format',
            ], 400);
        }

        // Check if recipe exists
        if (! $recipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe not found',
            ], 404);
        }

        // Check if user has access to this recipe
        if (! $this->recipeService->userCanAccessRecipe(Auth::id(), $recipe)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this recipe',
            ], 403);
        }

        // Return HTML view for printing
        return view('recipes.print', compact('recipe'))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Export a recipe in the specified format.
     *
     * @param  string  $id
     * @param  string  $format
     * @return \Illuminate\Http\Response
     */
    public function exportRecipe($id, $format)
    {
        $recipe = Recipe::find($id);

        // Check if recipe exists
        if (! $recipe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Recipe not found',
            ], 404);
        }

        // Check if user has access to this recipe
        if (! $this->recipeService->userCanAccessRecipe(Auth::id(), $recipe)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this recipe',
            ], 403);
        }

        // Check if user has Tier 1 access for export functionality
        $user = Auth::user();
        if (! $user->hasTier1Access() && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export functionality is only available for Tier 1 and Tier 2 subscribers',
            ], 403);
        }

        switch ($format) {
            case 'pdf':
                $pdf = $this->pdfService->generateRecipePDF($recipe);

                return $pdf->download('recipe-'.$recipe->id.'.pdf');
            case 'txt':
                $content = $this->recipeService->generateRecipeText($recipe);

                return response($content)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="recipe-'.$recipe->id.'.txt"');
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unsupported export format',
                ], 400);
        }
    }
}
