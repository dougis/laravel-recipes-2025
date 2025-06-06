<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    /**
     * Display a listing of sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $sources = Source::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $sources
        ]);
    }
}
