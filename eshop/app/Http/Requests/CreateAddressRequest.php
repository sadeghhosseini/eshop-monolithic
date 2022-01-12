<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
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
            'province' => 'required|string|min:2|max:100',
            'city' => 'required|string|min:2|max:100',
            'rest_of_address' => 'required|string|min:5|max:200',
            'postal_code' => 'required|min:5|max:100',
            'customer_id' => ['required', new ForeignKeyExists(User::class)]
        ];
    }
}
