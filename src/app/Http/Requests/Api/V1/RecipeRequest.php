<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'ingredients' => 'required|string',
            'instructions' => 'required|string',
            'notes' => 'nullable|string',
            'servings' => 'nullable|integer|min:1',
            'source_id' => 'nullable|string|exists:sources,_id',
            'classification_id' => 'nullable|string|exists:classifications,_id',
            'date_added' => 'nullable|date',
            'calories' => 'nullable|integer|min:0',
            'fat' => 'nullable|numeric|min:0',
            'cholesterol' => 'nullable|numeric|min:0',
            'sodium' => 'nullable|numeric|min:0',
            'protein' => 'nullable|numeric|min:0',
            'marked' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'meal_ids' => 'nullable|array',
            'meal_ids.*' => 'string|exists:meals,_id',
            'preparation_ids' => 'nullable|array',
            'preparation_ids.*' => 'string|exists:preparations,_id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'string|exists:courses,_id',
            'is_private' => 'nullable|boolean',
        ];
    }
}
