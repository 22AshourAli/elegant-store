<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class BackfillDeliveredAt extends Command
{
    protected $signature = 'orders:backfill-delivered-at
        {--dry-run : Show what would be changed without applying}
        {--from= : Only process orders updated after this date (Y-m-d)}';

    protected $description = 'Set delivered_at for delivered orders that lack it, using updated_at as approximation';

    public function handle(): int
    {
        $query = Order::where('status', OrderStatus::Delivered->value)
            ->whereNull('delivered_at');

        if ($from = $this->option('from')) {
            $query->where('updated_at', '>=', $from);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No delivered orders without delivered_at found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$orders->count()} order(s) missing delivered_at.");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'User', 'Total', 'Updated At', 'Estimated Delivery'],
                $orders->map(fn ($o) => [
                    $o->id,
                    $o->user_id,
                    $o->total,
                    $o->updated_at->format('Y-m-d H:i'),
                    $o->updated_at->format('Y-m-d H:i'),
                ])
            );
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            $order->timestamps = false;
            $order->delivered_at = $order->updated_at;
            $order->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$orders->count()} order(s) with delivered_at = updated_at.");

        return Command::SUCCESS;
    }
}
