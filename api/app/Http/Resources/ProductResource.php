<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->resource->getUuid(),
            'name'        => $this->resource->getName(),
            'slug'        => $this->resource->getSlug(),
            'price'       => round($this->resource->getPrice(), 2),
            'description' => $this->resource->getDescription(),
            'category'    => $this->resource->getCategory()->jsonSerialize(),
            'tags'        => array_map(
                fn($tag) => $tag->jsonSerialize(),
                $this->resource->getTags()
            ),
            'created_at'  => $this->resource->getCreatedAt(),
            'updated_at'  => $this->resource->getUpdatedAt(),
        ];
    }
}
