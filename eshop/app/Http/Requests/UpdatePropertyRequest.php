<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'nullable|string|min:3',
            'is_visible' => 'nullable|boolean',
            'category_id' => ['nullable', new ForeignKeyExists(Category::class)],
        ];
    }
}
