<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Console\Command;

class BackfillReturnRequests extends Command
{
    protected $signature = 'returns:backfill';
    protected $description = 'Create ReturnRequest records for existing returned orders that lack one.';

    public function handle(): int
    {
        $orders = Order::where('status', 'returned')
            ->whereDoesntHave('returnRequests')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No returned orders without a return request found.');
            return 0;
        }

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            ReturnRequest::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'status' => 'approved',
                'reason' => 'تم الإرجاع بواسطة الإدارة',
                'approved_at' => now(),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Created {$orders->count()} return request(s) for existing returned orders.");

        return 0;
    }
}
