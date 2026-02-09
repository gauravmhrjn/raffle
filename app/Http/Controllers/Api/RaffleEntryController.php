<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CancelRaffleEntryAction;
use App\Actions\CreateRaffleEntryAction;
use App\DTOs\RaffleEntryDTO;
use App\Enum\RaffleStatus;
use App\Exceptions\AddressNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelRaffleEntryRequest;
use App\Http\Requests\CreateRaffleEntryRequest;
use App\Repositories\AddressRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class RaffleEntryController extends Controller
{
    public function store(
        CreateRaffleEntryRequest $request,
        ProductRepositoryInterface $productRepository,
        AddressRepositoryInterface $addressRepository,
        CreateRaffleEntryAction $createRaffleEntryAction
    ): JsonResponse {
        try {
            $address = $addressRepository->findAddressByIdForCurrentUser(
                $request->integer('address_id'),
                $request->user()->id
            );

            $product = $productRepository->findActiveProductById(
                $request->integer('product_id')
            );

            $raffleEntryDTO = new RaffleEntryDTO(
                $request->user(),
                $address,
                $product,
                RaffleStatus::PENDING,
                $request->string('payment_token')->value()
            );

            $raffleEntry = $createRaffleEntryAction->handle($raffleEntryDTO);

            return response()->json([
                'status' => 'success',
                'entry_code' => $raffleEntry->entry_code,
            ], $raffleEntry->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);

        } catch (AddressNotFoundException $exception) {
            $errorMessage = AddressNotFoundException::ERROR_MESSAGE;
        } catch (ProductNotFoundException $exception) {
            $errorMessage = ProductNotFoundException::ERROR_MESSAGE;
        } catch (\Exception $exception) {
            $errorMessage = 'Something went wrong';
        }

        return response()->json([
            'status' => 'failed',
            'error' => $errorMessage,
        ], Response::HTTP_BAD_REQUEST);
    }

    public function destroy(
        CancelRaffleEntryRequest $request,
        ProductRepositoryInterface $productRepository,
        CancelRaffleEntryAction $cancelRaffleEntryAction
    ): JsonResponse {
        try {
            $product = $productRepository->findActiveProductById(
                $request->integer('product_id')
            );

            $cancelRaffleEntryAction->handle($request->user(), $product);

            return response()->json([
                'status' => 'success',
            ], Response::HTTP_OK);
        } catch (ProductNotFoundException $exception) {
            return response()->json([
                'status' => 'failed',
                'error' => ProductNotFoundException::ERROR_MESSAGE,
            ], Response::HTTP_OK);
        }
    }
}
