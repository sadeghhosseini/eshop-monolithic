<?php

namespace Tests\Integration\Addresses;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\MyTestCase;

/**
* @testdox POST /api/addresses
*/

class PostAddressTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/addresses';
    }

    public function test_creates_address()
    {
        $this->actAsUserWithPermission('add-address-own');
        $address = Address::factory()->make()->makeHidden('customer_id')->toArray();
        $response = $this->post($this->getUrl(), $address);
        $response->assertCreated();
        expect(
            Address::find($response->json()['data']['id'])->toArray()
        )->toMatchArray(
            $address
        );
    }

    public function dataset_test_returns_400_if_inputs_are_invalid()
    {
        return [
            ['province', ''], //province => required
            ['city', ''], //city => required
            ['rest_of_address', ''], //rest_of_address => required
            ['postal_code', ''], //postal_code => required
        ];
    }

    /**
     * @dataProvider dataset_test_returns_400_if_inputs_are_invalid
     */
    public function test_returns_400_if_inputs_are_invalid($key, $value)
    {
        $this->actAsUserWithPermission('add-address-own');
        $address = Address::factory([
            $key => $value,
        ])->make()->makeHidden('customer_id')->toArray();
        $response = $this->post($this->getUrl(), $address);
        $response->assertStatus(400);
    }



    public function test_returns_401_if_not_logged_in()
    {
        $address = Address::factory()->make()->makeHidden('customer_id');
        $response = $this->post($this->getUrl(), $address->toArray());
        $response->assertStatus(401);
    }
    public function test_returns_401_if_user_is_not_authenticated()
    {
        $item = Address::factory()->make();
        $response = $this->post(
            $this->getUrl(),
            $item->toArray()
        );
        $response->assertUnauthorized();
    }
    public function test_returns_403_if_user_is_not_permitted()
    {
        $this->actAsUser();
        $item = Address::factory()->make();
        $response = $this->post(
            $this->getUrl(),
            $item->toArray()
        );
        $response->assertForbidden();
    }
}
