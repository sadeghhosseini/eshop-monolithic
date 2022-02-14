<?php

namespace Tests\Integration\Addresses;

use App\Helpers;
use App\Models\Address;
use Tests\MyTestCase;

class GetAddressesTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/addresses';
    }



    /**
     * @testdox get addresses of all users 
     */
    public function testGetAddressesOfAllUsers()
    {
        $user = $this->actAsUserWithPermission('view-address-any');
        $addresses = Address::factory()->count(5)->create();
        $response = $this->rget();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertEqualArray(
            $addresses->toArray(),
            $data,
            ['id', 'city', 'province', 'postal_code'],
            exactEquality: true,
        );
    }

    
    /**
    * @testdox get all addresses of all users filter by city
    */
    public function testGetAllAddressesOfAllUsersFilterByCity() {
        $user = $this->actAsUserWithPermission('view-address-any');
        $addresses[] = Address::factory(['city' => 'Chicago'])->create();
        $addresses[] = Address::factory(['city' => 'New York'])->create();
        $addresses[] = Address::factory(['city' => 'London'])->create();
        $addresses[] = Address::factory(['city' => 'London'])->create();
        $addresses[] = Address::factory(['city' => 'London'])->create();
        $response = $this->rget(qs: '?filter={"city":"London"}');
        $data = $this->getResponseBodyAsArray($response)['data'];
        $londonAddresses = array_filter($addresses, fn($address) => $address['city'] == 'London');
        $this->assertEqualArray(
            $londonAddresses,
            $data,
            ['id', 'city', 'province', 'postal_code'],
            exactEquality: true,
        );
    }


    /**
     * @testdox get own addresses
     */
    public function testGetOwnAddresses()
    {
        $user = $this->actAsUserWithPermission('view-address-own');
        $addresses = Address::factory(['customer_id' => $user->id])->count(3)->create();
        $otherAddresses = Address::factory()->count(10)->create();
        $response = $this->rget();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertEqualArray(
            $addresses->toArray(),
            $data,
            ['id', 'city', 'province', 'postal_code'],
            exactEquality: true,
        );
    }


    
    /**
    * @testdox get addresses with pagination
    */
    public function testGetAddressesWithPagination() {
        $user = $this->actAsUserWithPermission('view-address-any');
        $addresses = Address::factory()->count(100)->create();
        $response = $this->rget(qs:"?offset=10&limit=50");
        $response->assertOk();
        $data = $this->getResponseBodyAsArray($response)['data'];
        $this->assertCount(50, $data);
        $this->assertEqualArray(
            $addresses->splice(10, 50),
            $data,
            exactEquality: true,
        );
    }
}
