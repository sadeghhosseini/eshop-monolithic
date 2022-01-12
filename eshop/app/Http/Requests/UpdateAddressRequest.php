<?php

namespace App\Http\Requests;

use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
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
            'province' => 'nullable|string|min:2|max:100',
            'city' => 'nullable|string|min:2|max:100',
            'rest_of_address' => 'nullable|string|min:5|max:200',
            'postal_code' => 'nullable|min:5|max:100',
        ];
    }
}
