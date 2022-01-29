<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Requests\UpdateCartItemsRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
class CartController extends Controller
{
    /**
     * TODO test
     */
    #[Post('/carts/items')]
    public function addItem(AddCartItemRequest $request)
    {
        $isArrayOfItems = count($request->all()) !== count($request->all(), COUNT_RECURSIVE);
        $cart = Cart::firstOrCreate(['customer_id' => $request->user()->id]);
        if ($isArrayOfItems) {
            $items = $request->all();
            $attachInput = collect($items)->mapWithKeys(function ($item) {
                return [$item['product_id'] => collect($item)->only('quantity')];
            })->toArray();
            $cart->items()->attach($attachInput);
        } else {
            $cart->items()->attach($request->product_id, collect($request->all())->only('quantity')->toArray());
        }
        return response()->json([]);
    }

    /**
     * TODO test
     */
    #[Delete('/carts/items/{product}')]
    public function deleteItem(Request $request, Product $product)
    {
        if ($request->user()->cart()->exists()) {
            $request->user()->cart->items()->detach($product->id);
        }
        return response()->json($product->id);
    }

    #[Get('/carts/items')]
    public function getItems(Request $request)
    {
        return response()->json($request->user()->cart->items);
    }

    #[Patch('/carts/items/{product}')]
    public function updateItem(UpdateCartItemRequest $request, Product $product)
    {
        $cart = Cart::firstOrCreate(['customer_id' => $request->user()->id]);
        $cart->items()->updateExistingPivot($request->product_id, ['quantity' => $request->quantity]);
        return response()->json([]);
    }

    #[Patch('/carts/items')]
    public function updateItems(UpdateCartItemsRequest $request, Product $product)
    {
        $cart = Cart::firstOrCreate(['customer_id' => $request->user()->id]);
        $items = $request->all();
        foreach ($items as $item) {
            $cart->items()->updateExistingPivot($item['product_id'], collect($item)->only('quantity')->toArray());
        }
        return response()->json([]);
    }
}
