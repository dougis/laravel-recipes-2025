<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Preparation;
use Illuminate\Http\Request;

class PreparationController extends Controller
{
    /**
     * Display a listing of preparation methods.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $preparations = Preparation::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $preparations
        ]);
    }
}
