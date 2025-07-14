<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * List all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUsers(Request $request)
    {
        $query = User::query();

        // Apply filters
        if ($request->has('subscription_tier')) {
            $query->where('subscription_tier', $request->subscription_tier);
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }

        // Paginate results
        $users = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    /**
     * Get user details.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserDetails($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        // Get user statistics
        $recipeCount = $user->recipeCount();
        $cookbookCount = $user->cookbookCount();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'recipe_count' => $recipeCount,
                'cookbook_count' => $cookbookCount,
            ],
        ]);
    }

    /**
     * Update user.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$id,
            'subscription_tier' => 'sometimes|integer|min:0|max:100',
            'subscription_status' => 'sometimes|string|in:active,canceled,trial',
            'subscription_expires_at' => 'sometimes|nullable|date',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Toggle admin override for a user.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAdminOverride($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $user->admin_override = ! $user->admin_override;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Admin override toggled successfully',
            'data' => $user,
        ]);
    }

    /**
     * List all subscriptions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listSubscriptions()
    {
        $subscriptions = Subscription::all();

        return response()->json([
            'status' => 'success',
            'data' => $subscriptions,
        ]);
    }

    /**
     * Get system statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemStatistics()
    {
        // Get user counts by subscription tier
        $usersByTier = User::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$subscription_tier',
                        'count' => ['$sum' => 1],
                    ],
                ],
                [
                    '$sort' => ['_id' => 1],
                ],
            ]);
        });

        // Format results
        $tierCounts = [
            'free' => 0,
            'tier1' => 0,
            'tier2' => 0,
            'admin' => 0,
        ];

        foreach ($usersByTier as $tier) {
            if ($tier['_id'] === 0) {
                $tierCounts['free'] = $tier['count'];
            } elseif ($tier['_id'] === 1) {
                $tierCounts['tier1'] = $tier['count'];
            } elseif ($tier['_id'] === 2) {
                $tierCounts['tier2'] = $tier['count'];
            } elseif ($tier['_id'] === 100) {
                $tierCounts['admin'] = $tier['count'];
            }
        }

        // Get total counts
        $totalUsers = User::count();
        $totalRecipes = \App\Models\Recipe::count();
        $totalCookbooks = \App\Models\Cookbook::count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_users' => $totalUsers,
                'total_recipes' => $totalRecipes,
                'total_cookbooks' => $totalCookbooks,
                'users_by_tier' => $tierCounts,
            ],
        ]);
    }
}
