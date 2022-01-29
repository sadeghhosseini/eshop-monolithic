<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\User;
use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCartItemsRequest extends FormRequest
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
            '*.product_id' => [
                'required',
                new ForeignKeyExists(Product::class, function ($productId) {
                    $user = User::find(Auth::id())->first();
                    return $user
                        ->cart
                        ->items()
                        ->where('product_id', $productId)->exists();
                })
            ],
            '*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
