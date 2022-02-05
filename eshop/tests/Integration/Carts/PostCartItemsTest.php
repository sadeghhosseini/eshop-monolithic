<?php

namespace Tests\Integration\Carts;

use App\Models\Cart;
use App\Models\Product;
use Tests\MyTestCase;


/**
* @testdox POST /api/carts/items
*/
class PostCartItemsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/carts/items';
    }


    /**
     * @testdox add an item to cart
     */

    public function testAddAnItemToCart()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $product = Product::factory()->create();
        $data = [
            'product_id' => $product->id,
            'quantity' => random_int(1, 50),
        ];
        $response = $this->post($this->getUrl(), $data);
        $response->assertOk();
        expect($cart->items->last()->pivot->product_id)->toEqual($product->id);
    }


    /**
     * @testdox add an item to cart - 2
     */

    public function testAddAnItemToCart2()
    {
        $user = $this->actAsUser();
        $product = Product::factory()->create();
        $data = [
            'product_id' => $product->id,
            'quantity' => random_int(1, 50),
        ];
        $response = $this->post($this->getUrl(), $data);
        $response->assertOk();
    }


    /**
     * @testdox returns 400 if product is added to cart for the 2nd time
     */

    public function testReturns400IfProductIsAddedToCartForThe2ndTime()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $product = Product::factory()->create();
        $cart->items()->attach([
            $product->id => [
                'quantity' => random_int(1, 50),
            ]
        ]);
        $data = [
            'product_id' => $product->id,
            'quantity' => random_int(1, 50),
        ];
        $response = $this->post($this->getUrl(), $data);
        $response->assertStatus(400);
        $response->assertJsonValidationErrorFor('product_id', null);
    }

    
    /**
    * @testdox add items to cart
    */
    
    public function testAddItemsToCart() {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $products = Product::factory()->count(10)->create();
        $data = $products->map(function($product) {
            return [
                'product_id' => $product->id,
                'quantity' => random_int(1, 10),
            ];
        })->toArray();
        $response = $this->post($this->getUrl(), $data);
        $response->assertOk();
        expect(count($cart->items))->toEqual(10);
    
    }
}
