<?php

namespace App\Providers;

use App\Events\OrderDelivered;
use App\Events\StockUpdated;
use App\Listeners\CheckLowStock;
use App\Listeners\CreditFirstOrderCashback;
use App\Listeners\SendDeliveryNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderDelivered::class => [
            CreditFirstOrderCashback::class,
            SendDeliveryNotification::class,
        ],
        StockUpdated::class => [
            CheckLowStock::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
