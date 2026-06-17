<?php

namespace App\Console\Commands;

use App\Enums\ExchangeStatus;
use App\Enums\OrderStatus;
use App\Enums\ReturnRequestStatus;
use App\Models\Exchange;
use App\Models\ReturnRequest;
use Illuminate\Console\Command;

class ExpireReturnRequests extends Command
{
    protected $signature = 'orders:expire-requests
        {--dry-run : Show what would be cancelled without applying}
        {--force : Skip confirmation prompt}';

    protected $description = 'Auto-cancel pending return/exchange requests past the 3-day delivery window';

    public function handle(): int
    {
        $expiredReturns = $this->expiredReturnRequests();
        $expiredExchanges = $this->expiredExchanges();

        $total = $expiredReturns->count() + $expiredExchanges->count();

        if ($total === 0) {
            $this->info('No expired pending requests found.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$total} expired request(s):");
        $this->line("  Return requests: {$expiredReturns->count()}");
        $this->line("  Exchange requests: {$expiredExchanges->count()}");

        if ($this->option('dry-run')) {
            if ($expiredReturns->isNotEmpty()) {
                $this->table(
                    ['Type', 'ID', 'Order ID', 'User ID', 'Created', 'Delivered At'],
                    $expiredReturns->map(fn ($r) => [
                        'Return',
                        $r->id,
                        $r->order_id,
                        $r->user_id,
                        $r->created_at->format('Y-m-d'),
                        $r->order->delivered_at?->format('Y-m-d') ?? 'N/A',
                    ])
                );
            }
            if ($expiredExchanges->isNotEmpty()) {
                $this->table(
                    ['Type', 'ID', 'Order ID', 'User ID', 'Created', 'Delivered At'],
                    $expiredExchanges->map(fn ($e) => [
                        'Exchange',
                        $e->id,
                        $e->order_id,
                        $e->user_id,
                        $e->created_at->format('Y-m-d'),
                        $e->order->delivered_at?->format('Y-m-d') ?? 'N/A',
                    ])
                );
            }
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Cancel {$total} expired request(s)?")) {
            $this->info('Aborted.');
            return Command::SUCCESS;
        }

        $now = now();
        $reason = __('return.auto_expired_reason', [], 'ar')
            ?: 'انتهت مهلة الـ 3 أيام من تاريخ التوصيل';

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($expiredReturns as $request) {
            $request->update([
                'status' => ReturnRequestStatus::Rejected->value,
                'rejected_at' => $now,
                'admin_note' => $reason,
            ]);
            $bar->advance();
        }

        foreach ($expiredExchanges as $exchange) {
            $exchange->update([
                'status' => ExchangeStatus::Rejected->value,
                'rejected_at' => $now,
                'admin_note' => $reason,
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Cancelled {$total} request(s) successfully.");

        return Command::SUCCESS;
    }

    private function expiredReturnRequests()
    {
        return ReturnRequest::where('status', ReturnRequestStatus::Pending->value)
            ->whereHas('order', function ($q) {
                $q->where('status', OrderStatus::Delivered->value)
                    ->whereNotNull('delivered_at');
            })
            ->get()
            ->filter(fn ($r) => !$r->order->isWithinReturnWindow())
            ->values();
    }

    private function expiredExchanges()
    {
        return Exchange::where('status', ExchangeStatus::Pending->value)
            ->whereHas('order', function ($q) {
                $q->where('status', OrderStatus::Delivered->value)
                    ->whereNotNull('delivered_at');
            })
            ->get()
            ->filter(fn ($e) => !$e->order->isWithinReturnWindow())
            ->values();
    }
}
