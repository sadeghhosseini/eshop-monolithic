<?php



namespace Tests\Integration\Orders;

use App\Helpers;
use App\Models\Address;
use App\Models\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderAddress;
use Tests\MyTestCase;

class PatchOrderTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/orders/{id}';
    }


    /**
     * @testdox returns 403 if has no permission
     */
    public function testReturns403IfHasNoPermission()
    {
        $user = $this->actAsUser();
        $order = Order::factory()->create();
        $response = $this->rpatch(['id', $order->id], [
            'status' => 'shipped',
        ]);
        $response->assertForbidden();

        $order = Order::factory(['customer_id' => $user->id])->create();
        $orderAddress = OrderAddress::factory(['order_id' => $order->id])->create();
        $newAddress = Address::factory(['customer_id' => $user->id])
            ->create();
        $response = $this->rpatch(['id', $order->id], [
            'address_id' => $newAddress->id,
        ]);
        $response->assertForbidden();
    }

    /**
     * @testdox returns 403 if has edit-order(address)-own permission and is not the owner
     */
    public function testReturns403IfHasEditOrderAddressOwnPermissionAndIsNotTheOwner()
    {
        $user = $this->actAsUserWithPermission('edit-order(address)-own');
        $order = Order::factory()->create(); //for another user
        $address = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->rpatch(['id', $order->id], ['address_id' => $address->id]);
        $response->assertForbidden();
    }

    /**
     * @testdox user cannot change his/her own order's address when order.status is shipped
     */
    public function testUserCannotChangeHisHerOwnOrderSAddressWhenOrderStatusIsShipped()
    {
        $user = $this->actAsUserWithPermission('edit-order(address)-own');
        $order = Order::factory(['customer_id' => $user->id])->create();
        $order->status = OrderStatusEnum::Shipped->value;
        $order->save();
        $address = Address::factory(['customer_id' => $user->id])->create();
        $response = $this->rpatch(['id', $address->id], ['address_id' => $address->id]);
        $response->assertForbidden();
    }

    /**
     * @testdox user with permission: edit-order(status)-any can change any order's status
     */
    public function testUserWithPermissionEditOrderStatusAnyCanChangeAnyOrderSStatus()
    {
        $user = $this->actAsUserWithPermission('edit-order(status)-any');
        $order = Order::factory()->create();
        $response = $this->rpatch(['id', $order->id], [
            'status' => 'shipped',
        ]);
        $response->assertOk();
    }

    /**
     * @testdox returns 400 if user has edit-order(status)-any permission but does not provide status in request
     */
    public function testReturns400IfUserHasEditOrderStatusAnyPermissionButDoesNotProvideStatusInRequest()
    {
        $user = $this->actAsUserWithPermission('edit-order(status)-any');
        $order = Order::factory(['customer_id' => $user->id])->create();
        $response = $this->rpatch(['id', $order->id]);
        $response->assertStatus(400);
    }

    /**
     * @testdox returns 400 if user has edit-order(address)-own permission but does not provide address_id or address fields(province - city - rest_of_address - postal_code) in request
     */
    public function testReturns400IfUserHasEditOrderAddressOwnPermissionButDoesNotProvideAddressIdOrAddressFieldsProvinceCityRestOfAddressPostalCodeInRequest()
    {
        $user = $this->actAsUserWithPermission('edit-order(address)-own');
        $order = Order::factory(['customer_id' => $user->id])->create();
        $response = $this->rpatch(['id', $order->id]);
        $response->assertStatus(400);
    }

    /**
     * @testdox updates order's address using address_id
     */
    public function testUpdatesOrderSAddressUsingAddressId()
    {
        $user = $this->actAsUserWithPermission('edit-order(address)-own');
        $order = Order::factory(['customer_id' => $user->id])->create();
        $orderAddress = OrderAddress::factory(['order_id' => $order->id])->create();
        $newAddress = Address::factory(['customer_id' => $user->id])
            ->create();
        $response = $this->rpatch(['id', $order->id], [
            'address_id' => $newAddress->id,
        ]);
        $response->assertOk();
        $body = $this->getResponseBody($response);
        expect(
            collect($body->data->address)->only(
                'province',
                'city',
                'rest_of_address',
                'postal_code',
            )->toArray()
        )->toMatchArray(
            $newAddress->only(
                'province',
                'city',
                'rest_of_address',
                'postal_code',
            )
        );
    }

    /**
     * @testdox updates order's address using address fields
     */
    public function testUpdatesOrderSAddressUsingAddressFields()
    {
        $user = $this->actAsUserWithPermission('edit-order(address)-own');
        $order = Order::factory(['customer_id' => $user->id])->create();
        OrderAddress::factory(['order_id' => $order->id])->create();
        $newAddress = Address::factory(['customer_id' => $user->id])
            ->make();

        $response = $this->rpatch(['id', $order->id], $newAddress->makeHidden('customer_id')->toArray());
        $response->assertOk();
        $body = $this->getResponseBody($response);


        

        $expected = $newAddress->only(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        );

        $actual = collect($body->data->address)->only(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        )->toArray();

        $this->assertCount(count($expected), $actual);
        foreach ($expected as $expectedKey => $expectedValue) {
            $this->assertEquals($expectedValue, $actual[$expectedKey]);
        }
    }
}
