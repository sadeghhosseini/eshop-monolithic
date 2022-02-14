<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Utils\QueryString\QueryString;
use App\Models\Address;
use App\Models\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Prefix('/api')]
#[Middleware('auth:sanctum')]
class OrderController extends Controller
{
    #[Post('/orders')]
    public function create(CreateOrderRequest $request)
    {
        $customer = $request->user();
        //validation cart must not be empty
        if ($customer->cart->items->isEmpty()) {
            throw new BadRequestHttpException('Cart is empty');
        }
        //create order for user
        $order = new Order();
        $order->customer_id = $customer->id;
        #ignore
        $order->status = OrderStatusEnum::Processing->value;

        $order->save();

        #copy the information of the given address of the user to order_address
        $order->address()->create(Address::find($request->address_id)->select(
            'province',
            'city',
            'rest_of_address',
            'postal_code',
        )->first()->toArray());
        //copy all cart items to order items
        $items = $customer->cart->items->mapWithKeys(function ($item) {
            return [
                $item->id => [
                    'quantity' => $item['pivot']['quantity'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                ]
            ];
        });
        $order->items()->attach($items);
        //empty the cart
        $customer->cart->items()->detach();

        return new OrderResource($order->with('items')->first());
    }



    #[Get('/orders', middleware: ['permission:view-order-any|view-order-own'])]
    public function getAll(Request $request)
    {
        $has_viewOrderAny_permission = $request->user()->hasPermissionTo('view-order-any');
        $has_viewOrderOwn_permission = $request->user()->hasPermissionTo('view-order-own');

        if (!$has_viewOrderAny_permission && !$has_viewOrderOwn_permission) {
            throw new AuthorizationException();
        }

        if ($has_viewOrderAny_permission) {
            $orderQb = QueryString::createFromModelClass(Order::class)
                ->filter(['customer_id', 'status'])
                ->getQueryBuilder();
            $orders = $orderQb->with('items')->get()->all();
            
            return OrderResource::collection($orders);
        }

        if ($has_viewOrderOwn_permission) {
            $customer = $request->user();
            $orders = QueryString::create($customer->orders())
                ->filter(['status'])
                ->getCollection();
            return OrderResource::collection($orders);
        }
    }

    #[Get('/orders/{order}', middleware: ['permission:view-order-any|view-order-own'])]
    public function get(Request $request, Order $order)
    {
        $has_viewOrderOwn_permission = $request->user()->hasPermissionTo('view-order-own');
        $has_viewOrderAny_permission = $request->user()->hasPermissionTo('view-order-any');
        $isOwner = Auth::id() == $order->customer->id;

        if (!$has_viewOrderAny_permission && !$has_viewOrderOwn_permission) {
            throw new AuthorizationException();
        }

        if (
            !$has_viewOrderAny_permission &&
            $has_viewOrderOwn_permission &&
            !$isOwner
        ) {
            throw new AuthorizationException();
        }
        return new OrderResource($order->with('items')->first());
    }

    #[Patch('/orders/{order}', middleware: ['permission:edit-order(address)-own|edit-order(status)-any'])]
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $has_editOrderAddressOwn_permission = $request->user()->hasPermissionTo('edit-order(address)-own');
        $has_editOrderStatusAny_permission = $request->user()->hasPermissionTo('edit-order(status)-any');
        $isOwner = Auth::id() == $order->customer->id;

        if (!$has_editOrderStatusAny_permission && !$has_editOrderAddressOwn_permission) {
            throw new AuthorizationException();
        }

        if (
            !$has_editOrderStatusAny_permission &&
            $has_editOrderAddressOwn_permission &&
            !$isOwner
        ) {
            throw new AuthorizationException();
        }

        if ($has_editOrderStatusAny_permission) { //can change any order's status
            $order->status = $request->status;
            $order->save();
        }

        if ($has_editOrderAddressOwn_permission && $isOwner) {
            if ($request->has('address_id')) {
                if ($order->status == OrderStatusEnum::Shipped->value) {
                    throw new AuthorizationException('Your order has been sent. You cannot change the address now.');
                }
                $address = Address::find($request->address_id);

                $order->address()->update($address->only([
                    'city',
                    'province',
                    'rest_of_address',
                    'postal_code',
                ]));
            } else { //has address info
                $order->address()->update($request->all([
                    'city',
                    'province',
                    'rest_of_address',
                    'postal_code',
                ]));
            }
        }
        // return RestResponseBuilder::create()->setData($order->with('address')->first())->respond();
        return new OrderResource($order->with('address')->first());
    }

    /**
     * TODO test
     */
    #[Get('/orders/{order}/items')]
    public function getItems(Order $order) {
        $items = QueryString::create($order->items())
            ->paginate()
            ->getCollection();
        return ProductResource::collection($items);
    }
}
