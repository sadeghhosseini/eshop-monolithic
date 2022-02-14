<?php

namespace App\Http\Controllers;

use App\Helpers;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\PropertyResource;
use App\Http\Utils\QueryString\QueryString;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
class CategoryController extends Controller
{
    #[Post('/categories', middleware: ['auth:sanctum', 'can:add-category'])]
    public function create(CreateCategoryRequest $request)
    {
        $newCategory = new Category();
        $newCategory->title = $request->title;
        $newCategory->description = $request->description;
        $newCategory->parent_id = $request->input('parent_id', null);
        $newCategory->save();

        // return response()->json($newCategory);
        return new CategoryResource($newCategory);
    }

    #[Get('/categories')]
    public function getAll(Request $request)
    {
        // $categories = Category::all();
        $categories = QueryString::createFromModelClass(Category::class)
            ->filter(['title', 'parent_id'])
            ->paginate()
            ->getCollection();
        return CategoryResource::collection($categories);
    }

    #[Get('/categories/{category}')]
    public function get(Category $category)
    {
        return new CategoryResource($category);
    }

    #[Delete('/categories/{category}', middleware: ['auth:sanctum', 'can:delete-category-any'])]
    public function delete(Category $category)
    {
        $category->delete();
        return new CategoryResource($category);
    }

    #[Patch('/categories/{category}', middleware: ['auth:sanctum', 'can:edit-category-any'])]
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->title = $request->input('title', $category->title);
        $category->description = $request->input('description', $category->description);
        $category->save();
        return new CategoryResource($category);
    }

    
    #[Get('/categories/{category}/products')]
    public function getProducts(Request $request, Category $category) {
        $products = QueryString::create($category->products())
            ->paginate()
            ->getCollection();
        return ProductResource::collection($products);
    }

    #[Get('/categories/{category}/properties')]
    public function getProperties(Request $request, Category $category) {
        $properties = QueryString::create($category->properties())
            ->paginate()
            ->getCollection();
        return PropertyResource::collection($properties);
    }
}
