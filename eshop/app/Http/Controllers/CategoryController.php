<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{

    public function create(CreateCategoryRequest $request)
    {
        $newCategory = Category::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        return response()->json($newCategory);
    }

    public function getAll()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function get(Category $category)
    {
        return response()->json($category);
    }

    public function delete(Category $category)
    {
        $category->delete();
        return response()->json([]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->title = $request->input('title', $category->title);
        $category->description = $request->input('description', $category->description);
        $category->save();
        return response()->json($category);
    }
}
