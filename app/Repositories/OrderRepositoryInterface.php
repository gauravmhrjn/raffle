<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Models\Order;
use App\Models\RaffleEntry;

interface OrderRepositoryInterface
{
    public function save(RaffleEntry $raffleEntry, OrderStatus $orderStatus, PaymentStatus $paymentStatus, string $paymentTransactionCode): ?Order;
}
