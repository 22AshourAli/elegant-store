<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewAdminNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /** @var int Number of times to attempt the broadcast job before failing. */
    public int $tries = 3;

    /** @var int Seconds to wait before retrying a failed broadcast. */
    public int $backoff = 5;

    public function __construct(
        public array $data
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin.notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NewAdminNotification';
    }
}
