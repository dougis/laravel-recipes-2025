<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Classification;

class ClassificationController extends Controller
{
    /**
     * Display a listing of classifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $classifications = Classification::all();

        return response()->json([
            'status' => 'success',
            'data' => $classifications,
        ]);
    }
}
