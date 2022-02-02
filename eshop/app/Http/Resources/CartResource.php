<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'customer_id' => $this->customer_id,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        ...$item->pivot->only(
                            'product_id',
                            'quantity',
                        ),
                        ...$item->only(
                            'title',
                            'description',
                            'category_id',
                            'price',
                        ),
                    ];
                });
            }),
        ];
    }
}
