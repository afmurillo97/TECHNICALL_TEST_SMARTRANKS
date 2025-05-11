<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' =>[
                'organization' => 'afmurillo97 Company',
                'final_tester' => 'SMART RANKS TECHNICAL TEST',
                'authors' => [
                    'Felipe Murillo',
                    'afmurillo97@gmail.com',
                    'afmurillo97'
                ]
            ]
        ];
    }
}
