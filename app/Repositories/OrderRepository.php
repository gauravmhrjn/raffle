<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Models\Order;
use App\Models\RaffleEntry;
use Illuminate\Support\Str;

final readonly class OrderRepository implements OrderRepositoryInterface
{
    public function save(
        RaffleEntry $raffleEntry,
        OrderStatus $orderStatus,
        PaymentStatus $paymentStatus,
        string $paymentTransactionCode
    ): Order {
        $raffleEntry->load('product');

        return Order::create([
            'order_code' => Str::uuid()->toString(),
            'raffle_entry_id' => $raffleEntry->id,
            'user_id' => $raffleEntry->user_id,
            'address_id' => $raffleEntry->address_id,
            'product_id' => $raffleEntry->product_id,
            'amount' => $raffleEntry->product->price,
            'status' => $orderStatus->value,
            'payment_status' => $paymentStatus->value,
            'payment_transaction_code' => $paymentTransactionCode,
        ]);
    }
}
