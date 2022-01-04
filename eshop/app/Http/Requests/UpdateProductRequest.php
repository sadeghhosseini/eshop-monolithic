<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Image;
use App\Models\Property;
use App\Rules\ForeignKeyExists;
use App\Rules\MinWord;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'title' => ['nullable', 'min:3'],
            'description' => ['nullable', new MinWord(3)],
            'quantity' => ['nullable', 'gte:0'],
            'price' => ['nullable'],
            'category_id' => ['nullable', new ForeignKeyExists(Category::class)],
            'new_images' => ['nullable'],
            'new_images.*' => ['file'],
            'image_ids' => ['nullable', new ForeignKeyExists(Image::class)],
            'property_ids' => ['nullable', new ForeignKeyExists(Property::class)],
            'new_properties' => ['nullable', 'array', 'min:1'],
            'new_properties.*' => [
                'string',
                'min:2',
                function ($attribute, $value, $fail) {
                    $this->category_id;
                    if (
                        Property::where('category_id', $this->category_id)
                        ->where('title', $value)
                        ->exists()
                    ) {
                        $fail("Property with id of ${value} already exists for category with id of $this->category_id");
                    }
                }
            ]
        ];
    }
}
