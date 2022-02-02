<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            ...$this->pivot->only(
                'product_id',
                'quantity',
            ),
            ...$this->only(
                'title',
                'description',
                'category_id',
                'price',
            ),
        ];
    }
}
