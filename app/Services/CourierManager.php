<?php

namespace App\Services;

use App\Services\Couriers\MockBosta;
use InvalidArgumentException;

class CourierManager
{
    protected array $drivers = [];

    public function __construct()
    {
        $this->drivers = [
            'bosta' => MockBosta::class,
        ];
    }

    public function driver(?string $name = null): CourierService
    {
        $name = $name ?? config('shipping.default_courier', 'bosta');

        if (!isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Courier driver [{$name}] not supported.");
        }

        $class = $this->drivers[$name];
        return new $class($this->getConfig($name));
    }

    public function register(string $name, string $class): void
    {
        $this->drivers[$name] = $class;
    }

    protected function getConfig(string $name): array
    {
        return config("shipping.couriers.{$name}", []);
    }
}
