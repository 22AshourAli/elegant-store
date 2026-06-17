<?php

namespace App\Console\Commands;

use App\Enums\ExchangeStatus;
use App\Enums\OrderStatus;
use App\Enums\ReturnRequestStatus;
use App\Models\Order;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Console\Command;

class RemindReturnWindowExpiry extends Command
{
    protected $signature = 'orders:remind-window-expiry
        {--dry-run : Show what would be sent without notifying}';

    protected $description = 'Send a reminder to customers whose return window expires in 1 day';

    public function handle(): int
    {
        $targetDate = now()->subDays(2);

        $orders = Order::where('status', OrderStatus::Delivered->value)
            ->whereNotNull('delivered_at')
            ->whereDate('delivered_at', $targetDate->format('Y-m-d'))
            ->whereDoesntHave('returnRequests', fn ($q) => $q->whereIn('status', [ReturnRequestStatus::Pending->value, ReturnRequestStatus::Approved->value]))
            ->whereDoesntHave('exchanges', fn ($q) => $q->whereIn('status', [ExchangeStatus::Pending->value, ExchangeStatus::Approved->value]))
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No customers need a window expiry reminder today.');
            return Command::SUCCESS;
        }

        $this->info("Found {$orders->count()} order(s) whose window expires tomorrow.");

        if ($this->option('dry-run')) {
            $this->table(
                ['Order ID', 'User ID', 'Delivered At', 'Days Elapsed'],
                $orders->map(fn ($o) => [
                    $o->id,
                    $o->user_id,
                    $o->delivered_at->format('Y-m-d'),
                    (int) $o->delivered_at->diffInDays(now()),
                ])
            );
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        $sent = 0;
        foreach ($orders as $order) {
            if (!$order->user) {
                $bar->advance();
                continue;
            }

            $daysAgo = (int) $order->delivered_at->diffInDays(now());

            try {
                $order->user->notify(new \App\Notifications\ReturnWindowExpiring($order));
                $sent++;
            } catch (\Exception $e) {
                $this->warn("Failed to notify user #{$order->user_id} for order #{$order->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sent {$sent} reminder(s).");

        return Command::SUCCESS;
    }
}
