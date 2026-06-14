<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AbandonedCartReminder;
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

        $this->info('Sending recovery notifications...');
        $reminders = $service->processRecovery((int) $this->option('batch'));

        $sent = 0;
        foreach ($reminders as $r) {
            if (!$r['user_id']) {
                continue;
            }

            $user = User::find($r['user_id']);
            if (!$user) {
                continue;
            }

            $user->notify(new AbandonedCartReminder(
                cart: $r['cart'] ?? null,
                recoveryUrl: $r['recovery_url'],
                couponCode: $r['coupon_code'],
                reminderCount: $r['reminder_count'],
            ));
            $sent++;

            $this->line("  Sent reminder to user #{$r['user_id']} — {$r['total']} EGP — coupon: {$r['coupon_code']}");
        }

        $this->info("Sent {$sent} recovery notifications.");

        return Command::SUCCESS;
    }
}
