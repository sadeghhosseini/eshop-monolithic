<?php

namespace Tests\Integration\Addresses;

use App\Models\Address;
use Tests\MyTestCase;

/**
* @testdox DELETE /api/addresses/{id}
*/
class DeleteAddressTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/addresses/{id}';
    }
    
    public function test_deletes_an_address()
    {
        $user = $this->actAsUserWithPermission('delete-address-own');
        $address = Address::factory([
            'customer_id' => $user->id,
        ])->create();
        $response = $this->delete($this->url('id', $address->id));
        $response->assertOk();
        expect(Address::where('id', $address->id)->exists())
            ->toBeFalse();
    }

    public function test_returns_404_if_address_does_not_exist()
    {
        $user = $this->actAsUserWithPermission('delete-address-own');
        $response = $this->delete($this->url('id', 1));
        $response->assertStatus(404);
    }

    public function test_returns_401_if_user_is_not_authenticated()
    {
        $item = Address::factory()->create();
        $response = $this->delete(
            $this->url('id', $item->id),
        );
        $response->assertUnauthorized();
    }

    public function test_returns_403_if_user_is_not_permitted()
    {
        $this->actAsUser();
        $item = Address::factory()->create();
        $response = $this->delete(
            $this->url('id', $item->id),
        );
        $response->assertForbidden();
    }
}
