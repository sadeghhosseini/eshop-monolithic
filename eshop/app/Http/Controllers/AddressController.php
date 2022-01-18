<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
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
    
    #[Post('/addresses', middleware:['permission:add-own-addresses'])]
    public function create(CreateAddressRequest $request) {
        $address = new Address();
        $address->city = $request->city;
        $address->province = $request->province;
        $address->rest_of_address = $request->rest_of_address;
        $address->postal_code = $request->postal_code;
        $address->customer_id = $request->user()->id;
        $address->save();
        return response()->json($address);
    }
    
    /**
     * TODO for a customer returns all his/her address
     * for admin returns all customers' address 
     */
    #[Get('/addresses', middleware: ['permission:view-own-addresses|view-any-addresses'])]
    public function getAll(Request $request) {
        if ($request->user()->hasPermissionTo('view-any-addresses')) {
            return response()->json(User::all());
        }
        
        if ($request->user()->hasPermissionTo('view-own-addresses')) {
            return response()->json(User::find(Auth::id()));
        }

    }

    #[Get('/addresses/{address}')]
    public function get(Address $address) {
        return response()->json($address);
    }

    #[Patch('/addresses/{address}', middleware: ['permission:edit-own-addresses'])]
    public function update(UpdateAddressRequest $request, Address $address) {
        if ($address->customer->id !== $request->user()->id) {
            throw new AuthorizationException();
        }
        $address->city ??= $request->city;
        $address->province ??= $request->province;
        $address->rest_of_address ??= $request->rest_of_address;
        $address->postal_code ??= $request->postal_code;
        $address->save();

        return response()->json();
    }

    #[Delete('/addresses/{address}', middleware: ['permission:delete-own-addresses'])]
    public function delete(Address $address) {
        if ($address->customer->id !== Auth::id()) {
            throw new AuthorizationException();
        }
        $address->delete();
        return response()->json($address);
    }
}
