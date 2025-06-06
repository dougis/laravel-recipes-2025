<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CookbookRequest;
use App\Models\Cookbook;
use App\Services\CookbookService;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CookbookController extends Controller
{
    protected $cookbookService;
    protected $pdfService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\CookbookService  $cookbookService
     * @param  \App\Services\PDFService  $pdfService
     * @return void
     */
    public function __construct(CookbookService $cookbookService, PDFService $pdfService)
    {
        $this->cookbookService = $cookbookService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of the user's cookbooks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $cookbooks = $this->cookbookService->getUserCookbooks($user->id);

        return response()->json([
            'status' => 'success',
            'data' => $cookbooks
        ]);
    }

    /**
     * Display a listing of public cookbooks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicCookbooks()
    {
        $cookbooks = $this->cookbookService->getPublicCookbooks();

        return response()->json([
            'status' => 'success',
            'data' => $cookbooks
        ]);
    }

    /**
     * Store a newly created cookbook in storage.
     *
     * @param  \App\Http\Requests\Api\V1\CookbookRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CookbookRequest $request)
    {
        $user = Auth::user();

        // Check if user can create more cookbooks
        if (!$user->canCreateCookbook()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have reached your cookbook limit. Please upgrade your subscription to create more cookbooks.'
            ], 403);
        }

        $cookbook = $this->cookbookService->createCookbook($user->id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Cookbook created successfully',
            'data' => $cookbook
        ], 201);
    }

    /**
     * Display the specified cookbook.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cookbook = $this->cookbookService->getCookbook($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user has access to this cookbook
        if (!$this->cookbookService->userCanAccessCookbook(Auth::id(), $cookbook)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this cookbook'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $cookbook
        ]);
    }

    /**
     * Update the specified cookbook in storage.
     *
     * @param  \App\Http\Requests\Api\V1\CookbookRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CookbookRequest $request, $id)
    {
        $cookbook = Cookbook::find($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user owns this cookbook
        if ($cookbook->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this cookbook'
            ], 403);
        }

        $cookbook = $this->cookbookService->updateCookbook($id, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Cookbook updated successfully',
            'data' => $cookbook
        ]);
    }

    /**
     * Remove the specified cookbook from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $cookbook = Cookbook::find($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user owns this cookbook
        if ($cookbook->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete this cookbook'
            ], 403);
        }

        $this->cookbookService->deleteCookbook($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Cookbook deleted successfully'
        ]);
    }

    /**
     * Toggle cookbook privacy.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function togglePrivacy($id)
    {
        $cookbook = Cookbook::find($id);
        $user = Auth::user();

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user owns this cookbook
        if ($cookbook->user_id != $user->id && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this cookbook'
            ], 403);
        }

        // Check if user has Tier 2 access for privacy control
        if (!$user->hasTier2Access() && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Privacy control is only available for Tier 2 subscribers'
            ], 403);
        }

        $cookbook = $this->cookbookService->toggleCookbookPrivacy($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Cookbook privacy updated successfully',
            'data' => $cookbook
        ]);
    }

    /**
     * Add recipes to cookbook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addRecipes(Request $request, $id)
    {
        $request->validate([
            'recipe_ids' => 'required|array',
            'recipe_ids.*' => 'string|exists:recipes,_id',
        ]);

        $cookbook = Cookbook::find($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user owns this cookbook
        if ($cookbook->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this cookbook'
            ], 403);
        }

        $cookbook = $this->cookbookService->addRecipesToCookbook($id, $request->recipe_ids);

        return response()->json([
            'status' => 'success',
            'message' => 'Recipes added to cookbook successfully',
            'data' => $cookbook
        ]);
    }

    /**
     * Remove recipe from cookbook.
     *
     * @param  string  $id
     * @param  string  $recipe_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeRecipe($id, $recipe_id)
    {
        $cookbook = Cookbook::find($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user owns this cookbook
        if ($cookbook->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this cookbook'
            ], 403);
        }

        $cookbook = $this->cookbookService->removeRecipeFromCookbook($id, $recipe_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Recipe removed from cookbook successfully',
            'data' => $cookbook
        ]);
    }

    /**
     * Reorder recipes in cookbook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderRecipes(Request $request, $id)
    {
        $request->validate([
            'recipe_order' => 'required|array',
        ]);

        $cookbook = Cookbook::find($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user owns this cookbook
        if ($cookbook->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update this cookbook'
            ], 403);
        }

        $cookbook = $this->cookbookService->reorderCookbookRecipes($id, $request->recipe_order);

        return response()->json([
            'status' => 'success',
            'message' => 'Recipes reordered successfully',
            'data' => $cookbook
        ]);
    }

    /**
     * Get printable version of a cookbook.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function getPrintableCookbook($id)
    {
        $cookbook = $this->cookbookService->getCookbookWithRecipes($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user has access to this cookbook
        if (!$this->cookbookService->userCanAccessCookbook(Auth::id(), $cookbook)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this cookbook'
            ], 403);
        }

        // Check if user has Tier 1 access for cookbook printing
        $user = Auth::user();
        if (!$user && !$cookbook->is_private) {
            // Allow public access to public cookbooks without login
        } else if (!$user->hasTier1Access() && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook printing is only available for Tier 1 and Tier 2 subscribers'
            ], 403);
        }

        $pdf = $this->pdfService->generateCookbookPDF($cookbook);

        return $pdf->download('cookbook-' . $cookbook->id . '.pdf');
    }

    /**
     * Export a cookbook in the specified format.
     *
     * @param  string  $id
     * @param  string  $format
     * @return \Illuminate\Http\Response
     */
    public function exportCookbook($id, $format)
    {
        $cookbook = $this->cookbookService->getCookbookWithRecipes($id);

        // Check if cookbook exists
        if (!$cookbook) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cookbook not found'
            ], 404);
        }

        // Check if user has access to this cookbook
        if (!$this->cookbookService->userCanAccessCookbook(Auth::id(), $cookbook)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this cookbook'
            ], 403);
        }

        // Check if user has Tier 1 access for export functionality
        $user = Auth::user();
        if (!$user && !$cookbook->is_private) {
            // Allow public access to public cookbooks without login
        } else if (!$user->hasTier1Access() && !$user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export functionality is only available for Tier 1 and Tier 2 subscribers'
            ], 403);
        }

        switch ($format) {
            case 'pdf':
                $pdf = $this->pdfService->generateCookbookPDF($cookbook);
                return $pdf->download('cookbook-' . $cookbook->id . '.pdf');
            case 'txt':
                $content = $this->cookbookService->generateCookbookText($cookbook);
                return response($content)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="cookbook-' . $cookbook->id . '.txt"');
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unsupported export format'
                ], 400);
        }
    }
}
