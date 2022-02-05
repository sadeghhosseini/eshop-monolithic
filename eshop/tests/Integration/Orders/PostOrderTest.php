<?php


namespace Tests\Orders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Tests\MyTestCase;

class PostOrderTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/orders';
    }


    /**
     * @testdox creats a new order
     */
    public function testCreatsANewOrder()
    {
        $user = $this->actAsUser();
        $address = Address::factory(['customer_id' => $user->id])->create();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $cartItemsCount = 5;
        $products = Product::factory()->count($cartItemsCount)->create();
        $dataToAttach = collect($products)->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'quantity' => random_int(1, 50),
                ]
            ];
        });
        $cart->items()->attach($dataToAttach);
        $response = $this->rpost(['address_id' => $user->addresses->first()->id]);
        $response->assertOk();
        $order = Order::where('customer_id', $user->id)->first();
        expect($order)->not()->toBeNull();

        expect($order->items->count())->toEqual($cartItemsCount);

        expect(
            $cart->items->isEmpty()
        )->toBeTrue();
        expect($order->address->city)->toEqual($address->city);
        expect($order->address->province)->toEqual($address->province);
        expect($order->address->postal_code)->toEqual($address->postal_code);
        expect($order->address->rest_of_address)->toEqual($address->rest_of_address);
    }


    /**
     * @testdox returns 400 if wrong address_id is provided
     */
    public function testReturns400IfWrongAddressIdIsProvided()
    {
        $user = $this->actAsUser();
        $address = Address::factory()->create(); //another user's address
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $cartItemsCount = 5;
        $products = Product::factory()->count($cartItemsCount)->create();
        $dataToAttach = collect($products)->mapWithKeys(function ($product) {
            return [
                $product->id => [
                    'quantity' => random_int(1, 50),
                ]
            ];
        });
        $cart->items()->attach($dataToAttach);
        $response = $this->rpost(['address_id' => $address->id]);
        $response->assertStatus(400);
    }


    /**
     * @testdox returns 400 if cart is empty
     */
    public function testReturns400IfCartIsEmpty()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $response = $this->rpost();
        $response->assertStatus(400);
    }
}
