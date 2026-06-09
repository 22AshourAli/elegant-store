<?php

namespace App\Console\Commands;

use App\Services\AbandonedCartService;
use Illuminate\Console\Command;

class ProcessAbandonedCarts extends Command
{
    protected $signature = 'carts:process-abandoned
        {--idle=120 : Minutes a cart must be idle before considered abandoned}
        {--batch=50 : Number of carts to process per run}';

    protected $description = 'Identify abandoned carts and send recovery notifications';

    public function handle(AbandonedCartService $service): int
    {
        $this->info('Identifying abandoned carts...');

        $marked = $service->identifyAbandonedCarts((int) $this->option('idle'));
        $this->info("Marked {$marked} carts as abandoned.");

        $this->info('Generating recovery notifications...');
        $recovered = $service->processRecovery((int) $this->option('batch'));
        $this->info("Processed " . count($recovered) . " recovery notifications.");

        foreach ($recovered as $r) {
            $this->line("  [{$r['cart_id']}] #{$r['user_id']} — {$r['total']} EGP — coupon: {$r['coupon_code']}");
        }

        return Command::SUCCESS;
    }
}
