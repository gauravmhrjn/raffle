<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Address;

interface AddressRepositoryInterface
{
    public function findAddressByIdForCurrentUser(int $addressId, int $userId): ?Address;
}
