<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\AddressNotFoundException;
use App\Models\Address;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class AddressRepository implements AddressRepositoryInterface
{
    public function findAddressByIdForCurrentUser(int $addressId, int $userId): Address
    {
        try {
            return Address::ofUserId($userId)->findOrFail($addressId);
        } catch (ModelNotFoundException $exception) {
            throw new AddressNotFoundException(
                AddressNotFoundException::ERROR_MESSAGE
            );
        }
    }
}
