<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\OrderStatusCast;
use App\Casts\PaymentStatusCast;
use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $order_code
 * @property OrderStatus $status
 * @property PaymentStatus $payment_status
 * @property int $raffle_entry_id
 * @property int $user_id
 * @property int $address_id
 * @property int $product_id
 * @property float $amount
 * @property string $payment_transaction_code
 * @property User $user
 * @property Address $address
 * @property Product $product
 * @property RaffleEntry $raffleEntry
 */
final class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_code',
        'status',
        'payment_status',
        'raffle_entry_id',
        'user_id',
        'address_id',
        'product_id',
        'amount',
        'payment_transaction_code',
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
            'order_code' => 'string',
            'status' => OrderStatusCast::class,
            'payment_status' => PaymentStatusCast::class,
            'user_id' => 'integer',
            'address_id' => 'integer',
            'product_id' => 'integer',
            'raffle_entry_id' => 'integer',
            'amount' => 'float',
            'payment_transaction_code' => 'string',
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

    public function raffleEntry(): BelongsTo
    {
        return $this->belongsTo(RaffleEntry::class);
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
    protected function forRaffleEntry(Builder $query, RaffleEntry $raffleEntry): void
    {
        $query->where('raffle_entry_id', $raffleEntry->id);
    }
}
