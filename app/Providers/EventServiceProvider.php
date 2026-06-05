<?php

namespace App\Providers;

use App\Events\OrderDelivered;
use App\Listeners\CreditFirstOrderCashback;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderDelivered::class => [
            CreditFirstOrderCashback::class,
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
