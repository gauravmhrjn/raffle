<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\AddressRepository;
use App\Repositories\AddressRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\RaffleEntryRepository;
use App\Repositories\RaffleEntryRepositoryInterface;
use App\Services\PaymentService;
use App\Services\PaymentServiceInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     */
    public array $bindings = [
        // repositories
        AddressRepositoryInterface::class => AddressRepository::class,
        OrderRepositoryInterface::class => OrderRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        RaffleEntryRepositoryInterface::class => RaffleEntryRepository::class,

        // services
        PaymentServiceInterface::class => PaymentService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    private function configureDefaults(): void
    {
        DB::whenQueryingForLongerThan(500, function (Connection $connection, QueryExecuted $event) {
            // Log the query and the time it took
            Log::warning('Slow query detected', [
                'query' => $event->sql,
                'bindings' => $event->bindings,
                'time' => $event->time,
            ]);
        });

        DB::prohibitDestructiveCommands(app()->isProduction());

        Model::preventLazyLoading(! app()->isProduction());

        Model::automaticallyEagerLoadRelationships();

        JsonResource::withoutWrapping();
    }
}
