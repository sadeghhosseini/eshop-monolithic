<?php

namespace Tests\Integration\Addresses;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\MyTestCase;

/**
* @testdox GET /api/addresses/{id}
*/

class GetAddressTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/addresses/{id}';
    }

    public function test_gets_an_address_by_id()
    {
        $user = $this->actAsUserWithPermission('view-address-own');
        $address = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->get($this->url('id', $address->id));
        $response->assertOk();
        expect($response->json()['data'])->toMatchArray($address->toArray());
    }

    public function test_gets_returns_404_if_address_not_found()
    {
        $user = $this->actAsUserWithPermission('view-address-own');
        $response = $this->get($this->url('id', 1));
        $response->assertStatus(404);
    }

    public function test_returns_401_if_user_is_not_authenticated()
    {
        $item = Address::factory()->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertUnauthorized();
    }

    public function test_returns_403_if_user_is_not_permitted()
    {
        $this->actAsUser();
        $item = Address::factory()->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertForbidden();
    }

    /**
     * @testdox test returns 403 if user has view-address-own permission but is not the owner
     */
    public function test_returns_403_if_user_has_viewAddressOwn_permission_but_is_not_the_owner()
    {
        $this->actAsUserWithPermission('view-address-own');
        $item = Address::factory()->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertForbidden();
    }
    /**
     * @testdox test returns 200 if user has view-address-any permission and is not the owner
     */
    public function test_returns_200_if_user_has_viewAddressAny_permission_and_is_not_the_owner()
    {
        $this->actAsUserWithPermission('view-address-any');
        $item = Address::factory()->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertOk();
    }
    /**
     * @testdox test returns 200 if user has view-address-any permission and is the owner
     */
    public function test_returns_200_if_user_has_viewAddressAny_permission_and_is_the_owner()
    {
        $user = $this->actAsUserWithPermission('view-address-any');
        $item = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertOk();
    }
    /**
     * @testdox test returns 200 if user has both view-address-any and view-address-own permission and is not the owner
     */
    public function test_returns_200_if_user_has_both_viewAddressAny_and_viewAddressOwn_permission_and_is_not_the_owner()
    {
        $user = $this->actAsUserWithPermission('view-address-any');
        $item = Address::factory()->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertOk();
    }

    /**
     * @testdox test returns 200 if user has both view-address-any and view-address-own permission and is the owner
     */
    public function test_returns_200_if_user_has_both_viewAddressAny_and_viewAddressOwn_permission_and_is_the_owner()
    {
        $user = $this->actAsUserWithPermission('view-address-any');
        $item = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->get($this->url('id', $item->id));
        $response->assertOk();
    }
}
