<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enum\RaffleStatus;
use App\Models\Address;
use App\Models\Product;
use App\Models\User;

final class RaffleEntryDTO
{
    public function __construct(
        public User $user,
        public Address $address,
        public Product $product,
        public RaffleStatus $status,
        public string $paymentToken,
    ) {}
}
