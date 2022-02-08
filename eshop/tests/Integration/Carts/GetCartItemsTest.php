<?php


namespace Tests\Carts;

use App\Models\Cart;
use App\Models\Product;
use Tests\MyTestCase;


/**
* @testdox GET /api/carts/items
*/

class GetCartItemsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/carts/items';
    }


    /**
     * @testdox returns all the cart items for the authenticated user
     */
    public function testReturnsAllTheCartItemsForTheAuthenticatedUser()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $products = Product::factory()->count(10)->create();
        $attachInput = collect($products)->mapWithKeys(function ($item) {
            return [$item->id => ['quantity' => random_int(5, 30)]];
        })->toArray();
        $cart->items()->attach($attachInput);
        $response = $this->get($this->getUrl());
        $response->assertOk();

        $body = $this->getResponseBody($response);
        expect(count($body->data->items))->toEqual(count($cart->items));
    }
}
