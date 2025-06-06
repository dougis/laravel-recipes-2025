<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserProfileRequest;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $subscriptionService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\SubscriptionService  $subscriptionService
     * @return void
     */
    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        $user = Auth::user();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'recipe_count' => $user->recipeCount(),
                'cookbook_count' => $user->cookbookCount(),
                'subscription' => [
                    'tier' => $user->subscription_tier,
                    'status' => $user->subscription_status,
                    'expires_at' => $user->subscription_expires_at,
                ],
            ]
        ]);
    }

    /**
     * Update user profile.
     *
     * @param  \App\Http\Requests\Api\V1\UserProfileRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UserProfileRequest $request)
    {
        $user = Auth::user();
        
        $user->update($request->validated());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Get user subscription details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubscription()
    {
        $user = Auth::user();
        
        $subscription = $this->subscriptionService->getUserSubscription($user->id);
        
        return response()->json([
            'status' => 'success',
            'data' => $subscription
        ]);
    }

    /**
     * Update user subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSubscription(Request $request)
    {
        $request->validate([
            'subscription_tier' => 'required|integer|min:0|max:2',
            'payment_method_id' => 'required_if:subscription_tier,1,2|string',
        ]);
        
        $user = Auth::user();
        
        try {
            $subscription = $this->subscriptionService->updateUserSubscription(
                $user->id,
                $request->subscription_tier,
                $request->payment_method_id ?? null
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Subscription updated successfully',
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update subscription: ' . $e->getMessage()
            ], 400);
        }
    }
}
