<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    /**
     * Display a listing of meals.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $meals = Meal::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $meals
        ]);
    }
}
