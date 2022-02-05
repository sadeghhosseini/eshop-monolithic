<?php


namespace Tests\Orders;

use App\Models\Order;
use App\Models\Product;
use Tests\MyTestCase;

class GetOrdersTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/orders';
    }


    private function dataset_testReturnsAllOrdersOfTheUserWithViewOrderOwnOrViewOrderAny()
    {
        return [
            ['view-order-own'], 
            ['view-order-any'],
        ];
    }
    /**
     * @dataProvider dataset_testReturnsAllOrdersOfTheUserWithViewOrderOwnOrViewOrderAny
     * @testdox returns all orders of the user with view-order-own or view-order-any
     */
    public function testReturnsAllOrdersOfTheUserWithViewOrderOwnOrViewOrderAny($role)
    {
        $user = $this->actAsUserWithPermission($role);
        $products[] = Product::factory()->count(5)->create();
        $orders[] = Order::factory(['customer_id' => $user->id])->create();
        $orders[0]->items()->attach(
            $products[0]->mapWithKeys(function ($product) {
                return [
                    $product->id => [
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                        'quantity' => random_int(1, 10),
                    ]
                ];
            })
        );
        $products[] = Product::factory()->count(5)->create();
        $orders[] = Order::factory(['customer_id' => $user->id])->create();
        $orders[1]->items()->attach(
            $products[1]->mapWithKeys(function ($product) {
                return [
                    $product->id => [
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                        'quantity' => random_int(1, 10),
                    ]
                ];
            })
        );

        $response = $this->get($this->getUrl());
        $response->assertOk();
        $body = $this->getResponseBody($response);
        expect(collect($body->data)->count())->toEqual(2);
        for ($i = 0; $i < count($orders); ++$i) {
            collect($orders[$i]->items)
                ->each(function ($item) use ($products, $i) {
                    expect($item->pivot->product_id)
                        ->toBeIn($products[$i]->map(function ($pr) {
                            return $pr->id;
                        })->toArray());
                });
        }
    }


    /**
     * @testdox returns all orders if permission is view-order-any
     */
    public function testReturnsAllOrdersIfPermissionIsViewOrderAny()
    {
        $user = $this->actAsUserWithPermission('view-order-any');
        $orders[] = Order::factory(['customer_id' => $user->id])->create();
        $orders[] = Order::factory()->create(); //another user

        $response = $this->get($this->getUrl());
        $response->assertOk();
        $body = $this->getResponseBody($response);
        expect(collect($body->data)->count())->toEqual(2);
    }
}
