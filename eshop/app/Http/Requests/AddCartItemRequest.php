<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
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
        $body = $this->request->all();
        $isArrayOfArrays = count($body) !== count($body, COUNT_RECURSIVE);
        if ($isArrayOfArrays) {//is array of items
            return [
                '*.product_id' => ['required', new ForeignKeyExists(Product::class)],
                '*.quantity' => ['required', 'integer', 'min:1'],
            ];
        } else {
            return [
                'product_id' => ['required', new ForeignKeyExists(Product::class)],
                'quantity' => ['required', 'integer', 'min:1'],
            ];
        }
    }
}
