<?php

namespace App\Http\Requests;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Models\Enums\OrderStatusEnum;
use App\Rules\ForeignKeyExists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
        $addressIdRequiredIfCondition = $this->user()->hasPermissionTo('edit-order(address)-own') &&
            !$this->has([
                'province',
                'city',
                'rest_of_address',
                'postal_code',
            ]);
        $addressInfoRequiredIfCondition = $this->user()
            ->hasPermissionTo('edit-order(address)-own') &&
            !$this->has(['address_id']);
        return [
            #edit-order(status)-any
            'status' => [
                Rule::requiredIf(
                    $this->user()
                        ->hasPermissionTo('edit-order(status)-any')
                ),
                new Enum(OrderStatusEnum::class)
            ],

            #edit-order(address)-own case-1
            'address_id' => [
                Rule::requiredIf($addressIdRequiredIfCondition),
                new ForeignKeyExists(existenceCheck: function ($addressId) {
                    $address = Address::where('id', $addressId)->first();
                    return $address?->customer?->id == $this->user()->id;//current user is the owner of the address
                })
            ],

            #update-order(address)-any case-2
            'province' => [Rule::requiredIf($addressInfoRequiredIfCondition), 'string', 'min:2', 'max:100'],
            'city' => [Rule::requiredIf($addressInfoRequiredIfCondition), 'string', 'min:2', 'max:100'],
            'rest_of_address' => [Rule::requiredIf($addressInfoRequiredIfCondition), 'string', 'min:5', 'max:200'],
            'postal_code' => [Rule::requiredIf($addressInfoRequiredIfCondition), 'min:5', 'max:100'],
        ];
    }
}
