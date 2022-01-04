<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Image;
use App\Models\Product;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function getAll()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function get(Product $product)
    {
        return response()->json($product);
    }

    public function create(CreateProductRequest $request)
    {
        $product = new Product();
        $product->title = $request->title;
        $product->description = $request->description;
        $product->quantity = $request->quantity;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->save();

        if ($request->has('image_ids')) {
            $product->images()->attach($request->image_ids);
        }
        if ($request->has('new_images')) {
            $paths = [];
            collect($request->new_images)->each(function ($image) use (&$paths) {
                $paths[] = Storage::putFile('images', $image);
            });
            if (!empty($paths)) {
                $product->images()->saveMany(
                    collect($paths)->map(fn ($path) => new Image(['path' => $path]))
                );
            }
        }
        if ($request->has('property_ids')) {
            $product->properties()->attach($request->property_ids);
        }
        if ($request->has('new_properties')) {
            $product->properties()
                ->saveMany(
                    collect($request->new_properties)
                        ->map(fn ($title) => new Property([
                            'title' => $title,
                            'category_id' => $request->category_id,
                        ]))
                );
        }

        return response()->json($product->with(['images', 'properties'])->get()->last());
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->title = $request->title ?? $product->title;
        $product->description = $request->description ?? $product->description;
        $product->quantity = $request->quantity ?? $product->quantity;
        $product->price = $request->price ?? $product->price;
        $product->category_id = $request->category_id ?? $product->category_id;
        $product->save();

        if ($request->has('image_ids')) {
            $product->images()->sync($request->image_ids);
        }
        if ($request->has('new_images')) {
            $paths = [];
            collect($request->new_images)->each(function ($image) use (&$paths) {
                $paths[] = Storage::putFile('images', $image);
            });
            if (!empty($paths)) {
                $product->images()->saveMany(
                    collect($paths)->map(fn ($path) => new Image(['path' => $path]))
                );
            }
        }
        if ($request->has('property_ids')) {
            $product->properties()->sync($request->property_ids);
        }
        if ($request->has('new_properties')) {
            $product->properties()
                ->saveMany(
                    collect($request->new_properties)
                        ->map(fn ($title) => new Property([
                            'title' => $title,
                            'category_id' => $request->category_id,
                        ]))
                );
        }

        return response()->json($product->with(['images', 'properties'])->get()->last());
    }

    /**
     * operations in respect to relatins:
     *      - remove from carts 
     *      - remove related comments 
     *      - make product_id in order_items null
     */
    public function delete(Product $product) {

    }
}
