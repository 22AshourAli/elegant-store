<?php

namespace App\Events;

use App\Models\ProductVariant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class StockUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $variantId,
        public int $productId,
        public ?int $branchId,
        public int $stockBefore,
        public int $stockAfter,
        public string $action,
        public ?int $orderId = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('stock'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'variant_id' => $this->variantId,
            'product_id' => $this->productId,
            'branch_id' => $this->branchId,
            'stock_before' => $this->stockBefore,
            'stock_after' => $this->stockAfter,
            'action' => $this->action,
            'order_id' => $this->orderId,
        ];
    }
}
