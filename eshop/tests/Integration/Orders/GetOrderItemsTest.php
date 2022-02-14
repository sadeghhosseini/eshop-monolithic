<?php

namespace Tests\Integration\Orders;

use App\Helpers;
use App\Models\Order;
use App\Models\Product;
use Tests\MyTestCase;

use function Tests\helpers\actAsUser;

class GetOrderItemsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/orders/{id}/items';
    }

    public function testGetItemsOfCertainOrder()
    {
        $user = actAsUser();
        $order = Order::factory()->create();
        $products = Product::factory()->count(3)->create();
        $order->items()->attach([
            $products[0]->id => $products[0]
                ->only('title', 'description', 'price', 'quantity'),
            $products[1]->id => $products[1]
                ->only('title', 'description', 'price', 'quantity'),
        ]);

        $response = $this->rget(['id', $order->id]);
        $data = $this->getResponseBodyAsArray($response)['data'];
        $response->assertOk();
        $this->assertEqualArray(
            [
                $products[0],
                $products[1],
            ],
            $data,
            fieldsToCheckEquality : [
                'id',
                'title',
                'description',
                'price',
            ],
            exactEquality: true,
        );
    }
}
