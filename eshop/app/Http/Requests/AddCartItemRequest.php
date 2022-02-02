<?php

namespace App\Http\Requests;

use App\Helpers;
use App\Models\Cart;
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
        if ($isArrayOfArrays) { //is array of items
            return [
                '*.product_id' => [
                    'required',
                    new ForeignKeyExists(Product::class),
                    function ($attribute, $value, $fail) {
                        $this->alreadyAddedToCart($attribute, $value, $fail);
                    },
                ],
                '*.quantity' => ['required', 'integer', 'min:1'],
            ];
        } else {
            return [
                'product_id' => [
                    'required',
                    new ForeignKeyExists(Product::class),
                    function ($attribute, $value, $fail) {
                        $this->alreadyAddedToCart($attribute, $value, $fail);
                    },
                ],
                'quantity' => ['required', 'integer', 'min:1'],
            ];
        }
    }

    private function alreadyAddedToCart($attribute, $value, $fail)
    {
        $cart = Cart::where('customer_id', $this->user()->id)
            ->first();
        if ($cart) {
            $exists = $cart->items()
                ->where('product_id', $value)
                ->exists();

            if ($exists) {
                $fail("Product is already added to the cart.");
            }
        }
    }
}
