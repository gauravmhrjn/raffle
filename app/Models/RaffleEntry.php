<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\RaffleStatusCast;
use App\Enum\RaffleStatus;
use Database\Factories\RaffleEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $entry_code
 * @property RaffleStatus $status
 * @property int $user_id
 * @property int $address_id
 * @property int $product_id
 * @property string $encrypted_payment_token
 * @property User $user
 * @property Address $address
 * @property Product $product
 * @property Order $order
 */
final class RaffleEntry extends Model
{
    /** @use HasFactory<RaffleEntryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'entry_code',
        'status',
        'user_id',
        'address_id',
        'product_id',
        'encrypted_payment_token',
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
            'entry_code' => 'string',
            'status' => RaffleStatusCast::class,
            'user_id' => 'integer',
            'address_id' => 'integer',
            'product_id' => 'integer',
            'encrypted_payment_token' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    #[Scope]
    protected function forUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    #[Scope]
    protected function forProduct(Builder $query, Product $product): void
    {
        $query->where('product_id', $product->id);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', RaffleStatus::PENDING->value);
    }
}
