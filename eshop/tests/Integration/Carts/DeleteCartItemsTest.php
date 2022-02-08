<?php


namespace Tests\Integration\Carts;

use App\Models\Cart;
use App\Models\Product;
use Tests\MyTestCase;


/**
* @testdox DELETE /api/carts/items/{id}
*/

class DeleteCartItemsTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/carts/items/{id}';
    }

    
    /**
    * @testdox it deletes an item from user's cart
    */
    
    public function testItDeletesAnItemFromUserSCart() {
        $user = $this->actAsUserWithPermission('delete-cart.item-own');
        $cart = Cart::factory(['customer_id' => $user->id])->create();
        $product = Product::factory([])->create();
        $cart->items()->attach($product->id, ['quantity' => 3]);
        $response = $this->delete($this->url('id', $product->id));
        $response->assertOk();
    }
}