<?php


namespace Tests\Orders;

use App\Models\Order;
use App\Models\Product;
use Tests\MyTestCase;

class GetOrderTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/orders/{id}';
    }


    /**
     * @testdox returns user's own orders if user has view-order-own
     */

    public function testReturnsUserSOwnOrdersIfUserHasViewOrderOwn()
    {
        $user = $this->actAsUserWithPermission('view-order-own');
        $products = Product::factory()->count(10)->create();
        $order = Order::factory(['customer_id' => $user->id])->create();
        $order->items()->attach(
            $products->mapWithKeys(function ($product) {
                return [
                    $product->id => [
                        'quantity' => random_int(1, 10),
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                    ]
                ];
            })
        );

        $response = $this->get($this->url('id', $order->id));
        $response->assertOk();
        $body = $this->getResponseBody($response);
        $products->each(function ($product) use ($body) {
            $items = $body->data->items;
            $foundProduct = collect($items)->filter(function ($item) use ($product) {
                return $item->product_id == $product->id;
            })->first();
            expect($product->only(
                'title',
                'description',
                'price',
            ))->toMatchArray(collect($foundProduct)->only(
                'title',
                'description',
                'price',
            ));
        });
    }


    /**
     * @testdox returns 403 if user is trying to get others orders
     */

    public function testReturns403IfUserIsTryingToGetOthersOrders()
    {
        $user = $this->actAsUserWithPermission('view-order-any');
        $products = Product::factory()->count(10)->create();
        $order = Order::factory()->create(); //another user's order
        $order->items()->attach(
            $products->mapWithKeys(function ($product) {
                return [
                    $product->id => [
                        'quantity' => random_int(1, 10),
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                    ]
                ];
            })
        );

        $response = $this->get($this->url('id', $order->id));
        $response->assertOk();
        $body = $this->getResponseBody($response);
        $products->each(function ($product) use ($body) {
            $order = $body;
            $items = $body->data->items;
            $foundProduct = collect($items)->filter(function ($item) use ($product) {
                return $item->product_id == $product->id;
            })->first();
            expect($product->only(
                'title',
                'description',
                'price',
            ))->toMatchArray(collect($foundProduct)->only(
                'title',
                'description',
                'price',
            ));
        });
    }


    /**
     * @testdox returns own order if user has view-order-any
     */

    public function testReturnsOwnOrderIfUserHasViewOrderAny()
    {
        $user = $this->actAsUserWithPermission('view-order-any');
        $orders = Order::factory(['customer_id' => $user->id])->create(); //own order

        $response = $this->get($this->url('id', $orders->id));
        $response->assertOk();
        $body = $this->getResponseBody($response);
        expect($body->data->id)->toEqual($orders->id);
    }


    /**
     * @testdox returns another user's order if user has view-order-any
     */

    public function testReturnsAnotherUserSOrderIfUserHasViewOrderAny()
    {
        $user = $this->actAsUserWithPermission('view-order-any');
        $orders = Order::factory()->create(); //another user's order

        $response = $this->get($this->url('id', $orders->id));
        $response->assertOk();
        $body = $this->getResponseBody($response);
        expect($body->data->id)->toEqual($orders->id);
    }


    /**
     * @testdox returns 403 if user is not owner and has view-order-own permission
     */

    public function testReturns403IfUserIsNotOwnerAndHasViewOrderOwnPermission()
    {
        $user = $this->actAsUserWithPermission('view-order-own');
        $orders = Order::factory()->create(); //another user's order
        $response = $this->get($this->url('id', $orders->id));
        $response->assertForbidden();
    }



    /**
     * @testdox returns 403 if user does not have any permission
     */

    public function testReturns403IfUserDoesNotHaveAnyPermission()
    {
        $user = $this->actAsUser();
        $orders = Order::factory()->create(); //another user's order
        $response = $this->get($this->url('id', $orders->id));
        $response->assertForbidden();
    }


    /**
     * @testdox returns 401 if user not authenticated
     */

    public function testReturns401IfUserNotAuthenticated()
    {
        $orders = Order::factory()->create(); //another user's order
        $response = $this->get($this->url('id', $orders->id));
        $response->assertUnauthorized();
    }
}
