<?php

use function Pest\Laravel\delete;
use function Tests\helpers\actAsUser;
use function Tests\helpers\actAsUserWithPermission;
use function Tests\helpers\getUrl;
use function Tests\helpers\printEndpoint;
use function Tests\helpers\setupAuthorization;
use function Tests\helpers\u;

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
setupAuthorization(fn ($closure) => beforeEach($closure));
/**
 * operations in respect to relatins:
 *      - remove from carts 
 *      - remove related comments 
 *      - make product_id in order_items null
 */
it("deletes product", function () use ($url) {
    actAsUserWithPermission('delete-product-any');
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
    $response = delete(u($url, 'id', $product->id));
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
});

it('returns 401 if user is not authenticated', function () use ($url) {
    $product = Product::factory()->create();
    $response = delete(u($url, 'id', $product->id));
    $response->assertUnauthorized();
});

it('returns 403 if user is not permitted', function () use ($url) {
    actAsUser();
    $product = Product::factory()->create();
    $response = delete(u($url, 'id', $product->id));
    $response->assertForbidden();
});
