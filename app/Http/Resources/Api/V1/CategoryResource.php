<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ProductResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_name' => $this->name,
            'description_excerpt' => $this->excerpt,
            'created_at' => $this->published_at,
            'url_image' => $this->featured_image,
            'products' => $this->when($this->relationLoaded('products'), function () {
                return ProductResource::collection($this->products);
            }),
        ];
    }
}
