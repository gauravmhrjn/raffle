<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Events\OrderCreated;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\RaffleEntryRepositoryInterface;
use App\Services\PaymentServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class ChargeWinnerAction
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private RaffleEntryRepositoryInterface $raffleEntryRepository,
        private PaymentServiceInterface $paymentService
    ) {}

    public function handle(int $raffleEntryId): void
    {
        $raffleEntry = $this->raffleEntryRepository->findById($raffleEntryId);

        DB::beginTransaction();

        $this->productRepository->decrementStockQty($raffleEntry->product_id);

        $paymentTransactionCode = $this->paymentService->processMocked();

        if (! $paymentTransactionCode) {
            DB::rollBack();

            // TODO: add log about failed payment transaction
            // TODO: select new winner as a replacement since the previous one failed
            return;
        }

        $order = $this->orderRepository->save(
            $raffleEntry,
            OrderStatus::COMPLETED,
            PaymentStatus::SUCCESS,
            $paymentTransactionCode,
        );

        $this->raffleEntryRepository->setWinner($raffleEntry);

        DB::commit();

        OrderCreated::dispatch($order);

        Log::info('ChargeWinnerAction executed', [
            'raffleEntryId' => $raffleEntryId,
            'orderId' => $order->id,
        ]);
    }
}
