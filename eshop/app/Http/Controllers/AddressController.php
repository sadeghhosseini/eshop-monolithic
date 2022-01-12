<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('/api')]
class AddressController extends Controller
{
    
    #[Post('/addresses')]
    public function create(CreateAddressRequest $request) {
        $address = new Address();
        $address->city = $request->city;
        $address->province = $request->province;
        $address->rest_of_address = $request->rest_of_address;
        $address->postal_code = $request->postal_code;
        $address->customer_id = $request->customer_id;
        $address->save();
        return response()->json($address);
    }
    
    /**
     * TODO for a customer returns all his/her address
     * for admin returns all customers' address 
     */
    #[Get('/addresses')]
    public function getAll() {
        
    }

    #[Get('/addresses/{address}')]
    public function get(Address $address) {
        return response()->json($address);
    }

    #[Patch('/addresses/{address}')]
    public function update(UpdateAddressRequest $request, Address $address) {
        $address->city ??= $request->city;
        $address->province ??= $request->province;
        $address->rest_of_address ??= $request->rest_of_address;
        $address->postal_code ??= $request->postal_code;
        $address->save();

        return response()->json();
    }

    #[Delete('/addresses/{address}')]
    public function delete(Address $address) {
        $address->delete();
        return response()->json($address);
    }
}
