<?php


namespace Tests\Integration\Products;

use App\Models\Cart;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\MyTestCase;

class DeleteProductTest extends MyTestCase
{
    public function getUrl()
    {
        return '/api/products/{id}';
    }


    /**
     * @testdox deletes product
     */
    public function testDeletesProduct()
    {
        $this->actAsUserWithPermission('delete-product-any');
        //create product
        //add comments to product
        $product = Product::factory()
            ->has(Comment::factory()->count(10))
            ->create();

        //create a user
        //create cart for user
        $user = User::factory()
            ->has(Cart::factory())
            ->has(Order::factory())
            ->create();

        //add product to cart of the user
        $user->cart->items()->attach($product->id, ['quantity' => 2]);

        //create order record in order_items
        $user->orders->last()->items()->attach(
            $product->id,
            $product->only('title', 'description', 'price', 'quantity')
        );
        //call end point
        $response = $this->rdelete(['id', $product->id]);
        $response->assertOk();
        //removes product from cart
        // expect(DB::table('cart_items')->where('product_id', $product->id)->exists())->toBeFalse();
        expect($product->cartItems()->exists())->toBeFalse();
        //removes related comments
        // expect(Comment::where('product_id', $product->id)->exists())->toBeFalse();
        expect($product->comments()->exists())->toBeFalse();
        //check if product is deleted
        expect(Product::find($product->id))->toBeNull();
        //makes order_items.product_id null
        expect($product->orderItems()->exists())->toBeFalse();
    }


    /**
     * @testdox returns 401 if user is not authenticated
     */
    public function testReturns401IfUserIsNotAuthenticated()
    {
        $product = Product::factory()->create();
        $response = $this->rdelete(['id', $product->id]);
        $response->assertUnauthorized();
    }


    /**
     * @testdox returns 403 if user is not permitted
     */
    public function testReturns403IfUserIsNotPermitted()
    {
        $this->actAsUser();
        $product = Product::factory()->create();
        $response = $this->rdelete(['id', $product->id]);
        $response->assertForbidden();
    }
}
