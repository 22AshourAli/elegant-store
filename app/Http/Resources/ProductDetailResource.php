<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Product $this */
        $variantGroups = $this->variants->groupBy('color');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'base_price' => (float) $this->base_price,
            'current_price' => (float) $this->current_price,
            'is_on_sale' => $this->is_on_sale,
            'has_variants' => $this->has_variants,
            'category' => new CategoryResource($this->whenLoaded('category')),

            'colors' => $variantGroups->keys()->filter()->values(),
            'sizes' => $this->variants->pluck('size')->filter()->unique()->values(),

            'color_galleries' => $this->colorGroupedImages(),

            'default_image' => $this->firstImageUrl(),
            'all_images' => $this->allImageUrls(),

            'variants' => $this->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'color' => $v->color,
                'size' => $v->size,
                'price' => (float) $v->current_price,
                'stock' => (int) $v->total_stock,
                'image' => $v->image_url,
                'is_default' => $v->is_default,
            ]),

            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }
}
