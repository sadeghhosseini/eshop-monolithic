<?php

use function Pest\Laravel\delete;
use function Tests\helpers\getUrl;
use function Tests\helpers\printEndpoint;

use App\Models\Cart;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);


$url = '/api/products/{id}';
beforeAll(function () use ($url) {
    printEndpoint('DELETE', $url);
});

/**
 * operations in respect to relatins:
 *      - remove from carts 
 *      - remove related comments 
 *      - make product_id in order_items null
 */
it("deletes product", function () {
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
    /*     echo "\nproducts: start\n";
    print_r($product->carts()->get()->toArray());
    echo "\nproduct.carts: end\n"; */
    
    //create order record in order_items
    $user->orders->last()->items()->attach(
        $product->id,
        $product->only('title', 'description', 'price', 'quantity')
    );
    
    //call end point
    $response = delete(getUrl($product->id));
    $response->assertOk(); 
    //removes product from cart
    expect(DB::table('cart_items')->where('product_id', $product->id)->exists())->toBeFalse();
    //removes related comments
    expect(Comment::where('product_id', $product->id)->exists())->toBeFalse();
    //check if product is deleted
    expect(Product::find($product->id))->toBeNull();
    //makes order_items.product_id null
});
