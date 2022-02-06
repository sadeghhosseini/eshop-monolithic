<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'id' => $this->id,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'commenter' => $this->whenLoaded('commenter', [
                'id' => $this->commenter->id,
                'name' => $this->commenter->name,
                'email' => $this->commenter->email,
            ]),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when($this->replies_count >= 0, $this->replies_count),
        ];
    }
}
