<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
class CategoryController extends Controller
{
    #[Post('/categories', middleware: ['auth:sanctum', 'can:add-categories'])]
    public function create(CreateCategoryRequest $request)
    {
        $newCategory = new Category();
        $newCategory->title = $request->title;
        $newCategory->description = $request->description;
        $newCategory->parent_id = $request->input('parent_id', null);
        $newCategory->save();

        return response()->json($newCategory);
    }

    #[Get('/categories')]
    public function getAll()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    #[Get('/categories/{category}')]
    public function get(Category $category)
    {
        return response()->json($category);
    }

    #[Delete('/categories/{category}', middleware: ['auth:sanctum', 'can:delete-any-categories'])]
    public function delete(Category $category)
    {
        $category->delete();
        return response()->json([]);
    }

    #[Patch('/categories/{category}', middleware: ['auth:sanctum', 'can:edit-any-categories'])]
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->title = $request->input('title', $category->title);
        $category->description = $request->input('description', $category->description);
        $category->save();
        return response()->json($category);
    }
}
