<?php

namespace App\Http\Controllers;

use App\Helpers;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Http\Utils\QueryString\QueryString;
use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
#[Middleware('auth:sanctum')]
class AddressController extends Controller
{
    
    #[Post('/addresses', middleware:['permission:add-address-own'])]
    public function create(CreateAddressRequest $request) {
        $address = new Address();
        $address->city = $request->city;
        $address->province = $request->province;
        $address->rest_of_address = $request->rest_of_address;
        $address->postal_code = $request->postal_code;
        $address->customer_id = $request->user()->id;
        $address->save();
        return new AddressResource($address);
    }
    
    #[Get('/addresses', middleware: ['permission:view-address-own|view-address-any'])]
    public function getAll(Request $request) {
        if ($request->user()->hasPermissionTo('view-address-any')) {
            $addresses = QueryString::createFromModelClass(Address::class)
                ->filter(['city', 'province', 'customer_id'])
                ->paginate()
                ->getCollection();
            return AddressResource::collection($addresses);
        }
        
        if ($request->user()->hasPermissionTo('view-address-own')) {
            $addressQb = QueryString::createFromModelClass(Address::class)
                ->filter(['city'])
                ->paginate()
                ->getQueryBuilder();
            $addresses = $addressQb->where('customer_id', Auth::id())->get();
            return AddressResource::collection($addresses);
        }

    }

    #[Get('/addresses/{address}', middleware: ['permission:view-address-own|view-address-any'])]
    public function get(Request $request, Address $address) {
        $isOwner = $request->user()->id == $address->customer->id;
        $has_viewAddressOwn_permission = $request->user()->hasPermissionTo('view-address-own');
        $has_viewAddressAny_permission = $request->user()->hasPermissionTo('view-address-any');
        $hasOnly_viewAddressOwn_permission = $has_viewAddressOwn_permission && !$has_viewAddressAny_permission;
        if (!$has_viewAddressAny_permission && !$has_viewAddressOwn_permission) {
            throw new AuthorizationException();
        }

        if ($hasOnly_viewAddressOwn_permission && !$isOwner) {
            throw new AuthorizationException();
        }

        return new AddressResource($address);
    }

    #[Patch('/addresses/{address}', middleware: ['permission:edit-address-own'])]
    public function update(UpdateAddressRequest $request, Address $address) {
        if ($address->customer->id !== $request->user()->id) {
            throw new AuthorizationException();
        }
        $address->city ??= $request->city;
        $address->province ??= $request->province;
        $address->rest_of_address ??= $request->rest_of_address;
        $address->postal_code ??= $request->postal_code;
        $address->save();

        return new AddressResource($address);
    }

    #[Delete('/addresses/{address}', middleware: ['permission:delete-address-own'])]
    public function delete(Address $address) {
        if ($address->customer->id !== Auth::id()) {
            throw new AuthorizationException();
        }
        $address->delete();
        return new AddressResource($address);
    }
}
