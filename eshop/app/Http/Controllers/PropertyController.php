<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
class PropertyController extends Controller
{
    #[Get('/properties/{property}')]
    public function get(Property $property) {
        // return response()->json($property);
        return new PropertyResource($property);
    }
    
    #[Get('/properties')]
    public function getAll() {
        $properties = Property::all();
        // return response()->json($properties);
        return PropertyResource::collection($properties);
    }
    
    #[Post('/properties', middleware: ['auth:sanctum', 'permission:add-property'])]
    public function create(CreatePropertyRequest $request) {
        $property = new Property();
        $property->title = $request->title;
        $property->is_visible = $request->is_visible;
        $property->category_id = $request->category_id;
        $property->save();
        // return response()->json($property);
        return new PropertyResource($property);
    }
    
    #[Patch('/properties/{property}', middleware: ['auth:sanctum', 'permission:edit-property-any'])]
    public function update(UpdatePropertyRequest $request, Property $property) {
        $property->title = $request->title ?? $property->title;
        $property->is_visible = $request->is_visible ?? $property->is_visible;
        $property->save();
        // return response()->json($property);
        return new PropertyResource($property);
    }
    
    #[Delete('/properties/{property}', middleware: ['auth:sanctum', 'permission:delete-property-any'])]
    public function delete(Property $property) {
        $property->delete();
        // return response()->json($property);
        return new PropertyResource($property);
    }
}
