<?php

namespace App\Http\Requests;

use App\Models\Address;
use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateOrderRequest extends FormRequest
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
            'address_id' => ['required', new ForeignKeyExists(existenceCheck: function($addressId) {
                $address = Address::where('id', $addressId)->first();
                return $address?->customer?->id == Auth::id();
            })]
        ];
    }
}
