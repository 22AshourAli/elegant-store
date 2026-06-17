<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'base_price' => (float) $this->base_price,
            'current_price' => (float) $this->current_price,
            'is_on_sale' => $this->is_on_sale,
            'image' => $this->firstImageUrl(),
            'colors' => $this->variants->pluck('color')->filter()->unique()->values(),
            'has_variants' => $this->has_variants,
            'category_id' => $this->category_id,
        ];
    }
}
