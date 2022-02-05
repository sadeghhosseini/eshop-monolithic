<?php

namespace Tests\Integration\Addresses;
use App\Models\Address;
use Tests\MyTestCase;


/**
* @testdox PATCH /api/addresses/{id}
*/

class UpdateAddressTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/addresses/{id}';
    }


    public function dataset_test_updates_address() {
        return [
            ['province'],
            ['city'],
            ['rest_of_address'],
            ['postal_code'],
        ];
    }

    /**
     * @dataProvider dataset_test_updates_address
     */
    public function test_updates_address($key) {
        $user = $this->actAsUserWithPermission('edit-address-own');
        $address = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->patch($this->url('id', $address->id), [
            ...$address->makeHidden($key)->toArray(),
            $key => Address::factory()->make()->$key,
        ]);
        $response->assertOk();
    
        $newAddress = Address::find($address->id);
        expect(
            $address->makeHidden($key)->toArray()
        )->toMatchArray(
            $newAddress->makeHidden($key)->toArray()
        );
        expect($address->$key)->toEqual($newAddress->$key);
    }
    
    public function dataset_test_returns_400_if_inputs_are_invalid() {
        return [
            ['province', 'a'],//province => min:2
            ['province', 132423423424],//province => string
            ['city', 'a'],//city => min:2
            ['city', 1123234],//city => string
            ['rest_of_address', 234234],//rest_of_address => string
        ];
    }

    /**
     * @dataProvider dataset_test_returns_400_if_inputs_are_invalid
     */
    public function test_returns_400_if_inputs_are_invalid($key, $value) {
        $user = $this->actAsUserWithPermission('edit-address-own');
        $address = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->patch($this->url('id', $address->id), [
            ...$address->toArray(),
            $key => $value,
        ]);
        $response->assertStatus(400);
    }
    
    public function test_returns_401_if_user_is_not_authenticated() {
        $item = Address::factory()->create();
        $response = $this->patch(
            $this->url('id', $item->id),
            Address::factory()->make()->toArray()
        );
        $response->assertUnauthorized();
    }
    public function test_returns_403_if_user_is_not_permitted() {
        $this->actAsUser();
        $item = Address::factory()->create();
        $response = $this->patch(
            $this->url('id', $item->id),
            Address::factory()->make()->toArray()
        );
        $response->assertForbidden();
    }
}
