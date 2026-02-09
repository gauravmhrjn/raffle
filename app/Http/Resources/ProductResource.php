<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $name
 * @property Brand $brand
 * @property Category $category
 * @property string $slug
 * @property string $sku
 * @property Carbon $raffle_date
 * @property string $image_url
 * @property float $price
 * @property string $description
 */
final class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'brand' => $this->brand->name,
            'category' => $this->category->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'raffle_date' => $this->raffle_date->toDateTimeString(),
            'image_url' => $this->image_url,
            'price' => $this->when($request->route()->named('product.show'), $this->price),
            'description' => $this->when($request->route()->named('product.show'), $this->description),
        ];
    }
}
