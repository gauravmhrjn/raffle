<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ProductStatusCast;
use App\Enum\ProductStatus;
use App\Observers\ProductObserver;
use Carbon\Carbon;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsUri;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property ProductStatus $status
 * @property int $category_id
 * @property int $brand_id
 * @property string $sku
 * @property string $slug
 * @property string $name
 * @property string $image_url
 * @property string $description
 * @property float $price
 * @property int $qty
 * @property Carbon $raffle_date
 * @property Brand $brand
 * @property Category $category
 */
#[ObservedBy(ProductObserver::class)]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'status',
        'category_id',
        'brand_id',
        'sku',
        'slug',
        'name',
        'image_url',
        'description',
        'price',
        'qty',
        'raffle_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'status' => ProductStatusCast::class,
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'sku' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'image_url' => AsUri::class,
            'description' => 'string',
            'price' => 'float',
            'qty' => 'integer',
            'raffle_date' => 'datetime: Y-m-d H:i',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', ProductStatus::ACTIVE->value);
    }

    #[Scope]
    protected function inactive(Builder $query): void
    {
        $query->where('status', ProductStatus::INACTIVE->value);
    }

    #[Scope]
    protected function raffled(Builder $query): void
    {
        $query->where('status', ProductStatus::RAFFLED->value);
    }

    #[Scope]
    protected function forSlug(Builder $query, string $slug): void
    {
        $query->where('slug', $slug);
    }

    #[Scope]
    protected function toRaffle(Builder $query): void
    {
        $query->where('raffle_date', '<=', now());
    }
}
