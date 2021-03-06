<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\CreateImageRequest;
use App\Http\Resources\ImageResource;
use App\Http\Resources\ProductResource;
use App\Http\Utils\QueryString\QueryString;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
class ImageController extends Controller
{

    #[Get('/images')]
    public function getAll(Request $request)
    {
        $images = QueryString::createFromModelClass(Image::class)
            ->paginate()
            ->getQueryBuilder()
            ->get();
        return new ImageResource($images);
    }

    #[Get('/images/{image}')]
    public function get(Image $image)
    {
        return new ImageResource($image);
    }

    #[Post('/images', middleware: ['auth:sanctum', 'permission:add-image'])]
    public function create(CreateImageRequest $request)
    {
        $images = $request->images;
        $paths = [];
        foreach ($images as $image) {
            $path = preg_replace("/^\/*/i", '', $image['path']);
            $path = empty($path) ? "/images" : "/images/$path";
            $paths[] = Storage::putFile($path, $image['file']);
        }

        $models = collect([]);
        foreach ($paths as $path) {
            $models->add(new Image());
            $models->last()->path = $path;
            $models->last()->save();
        }
        // return response()->json($models->toArray());
        return new ImageResource($models);
    }

    /**
     * changes the path|name of the image
     */
    /*  #[Patch('/images/{image}')]
    public function update(UpdateImageRequest $request, Image $image) {
        //move image on storage
        $oldPath = $image->path;
        $newPath = $request->path;
        Storage::move($oldPath, $newPath);
        //change path in db
        $image->path = $newPath;
        $image->save();
    } */

    #[Delete('/images/{image}', middleware: ['auth:sanctum', 'permission:delete-image-any'])]
    public function delete(Image $image)
    {
        $image->delete();
        Storage::delete($image->path);
        return response()->json($image);
    }

    /**
     * TODO test
     */
    #[Get('/images/{image}/products')]
    public function getProducts(Image $image)
    {
        return ProductResource::collection($image->products);
    }
}
