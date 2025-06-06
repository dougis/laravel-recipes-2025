<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CookbookRequest extends FormRequest
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
            'description' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'recipe_ids' => 'nullable|array',
            'recipe_ids.*.recipe_id' => 'required|string|exists:recipes,_id',
            'recipe_ids.*.order' => 'required|integer|min:0',
            'is_private' => 'nullable|boolean',
        ];
    }
}
