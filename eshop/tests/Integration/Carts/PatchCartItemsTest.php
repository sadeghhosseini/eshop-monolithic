<?php

namespace Tests\Integration\Carts;

use App\Models\Cart;
use App\Models\Product;
use Tests\MyTestCase;

/**
* @testdox PATCH /api/carts/items/{id}
*/

class PatchCartItemsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/carts/items/{id}';
    }

    /**
     * @testdox update an item's quantity to cart
     */
    public function testUpdateAnItemQuantityToCart()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $product = Product::factory()->create();
        $data = [
            'product_id' => $product->id,
            'quantity' => 5,
        ];
        $cart->items()->attach($product->id, ['quantity' => 5]);
        $newData = [
            'product_id' => $product->id,
            'quantity' => 13,
        ];
        $response = $this->patch($this->url('id', $product->id), $newData); 
        $response->assertOk();
        expect($cart->items->last()->pivot->quantity)->toEqual($newData['quantity']);
    }

    /**
     * @testdox add item to cart - mass update
     */

    public function testAddItemToCartMassUpdate()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $products = Product::factory()->count(10)->create();
        $data = $products->mapWithKeys(function ($product) {
            return [$product->id => [
                'product_id' => $product->id,
                'quantity' => random_int(1, 10),
            ]];
        })->toArray();
        $cart->items()->attach($data);
        $newData = $products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'quantity' => random_int(11, 20),
            ];
        })->toArray();
        $response = $this->patch($this->url('id', ''), $newData);
        $response->assertOk();
        foreach ($newData as $item) {
            expect(
                $cart->items()->where('product_id', $item['product_id'])->first()->pivot->quantity
            )->toEqual(
                $item['quantity']
            );
        }
    }

    /**
     * @testdox returns 400 if product is not already in the cart
     */
    public function testReturns400IfProductIsNotAlreadyInTheCart()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $product = Product::factory()->create();
        $cart->items()->attach($product->id, ['quantity' => 3]);
        $productNotInCart = Product::factory()->create();
        $data = [
            'product_id' => $productNotInCart->id,
            'quantity' => 5,
        ];
        $response = $this->patch($this->url('id', $data['product_id']), $data);
        $response->assertStatus(400);
    }



    /**
     * @testdox returns 400 if product is not already in the cart - mass update
     */

    public function testReturns400IfProductIsNotAlreadyInTheCartMassUpdate()
    {
        $user = $this->actAsUser();
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $product = Product::factory()->create();
        $cart->items()->attach($product->id, ['quantity' => 3]);
        $productNotInCart = Product::factory()->create();
        $data = [
            [
                'product_id' => $productNotInCart->id,
                'quantity' => 5,
            ],
            [
                'product_id' => $product->id,
                'quantity' => 9,
            ]
        ];
        $response = $this->patch($this->url('id', ''), $data);
        $response->assertStatus(400);
    }
}
