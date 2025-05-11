<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'category_id' => $this->category_id,
            'category' => $this->category->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float)$this->sale_price,
            'stock' => $this->stock,
            'created_at' => $this->published_at,
            'product_images' => [
                'featured_image' => $this->featured_image,
            ]
        ];
    }
}
