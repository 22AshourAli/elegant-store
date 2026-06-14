<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\LoyaltyPointsExpiring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyLoyaltyPointsExpiry extends Command
{
    protected $signature = 'loyalty:notify-expiry
        {--days=30 : Notify when points expire within this many days}
        {--batch=100 : Users to process per run}';

    protected $description = 'Send notifications to users whose loyalty points are about to expire';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $batch = (int) $this->option('batch');
        $cutoff = now()->addDays($days);

        $users = User::where('role', 'customer')
            ->whereHas('wallet', function ($q) {
                $q->where('loyalty_points', '>', 0);
            })
            ->limit($batch)
            ->get();

        $sent = 0;
        foreach ($users as $user) {
            $wallet = $user->wallet;
            if (!$wallet || $wallet->loyalty_points <= 0) {
                continue;
            }

            $hasExpiringSoon = DB::table('loyalty_points_log')
                ->where('user_id', $user->id)
                ->where('type', 'earned')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $cutoff)
                ->where('expires_at', '>', now())
                ->exists();

            if (!$hasExpiringSoon) {
                continue;
            }

            if ($wallet->last_expiry_notified_at && $wallet->last_expiry_notified_at->gt(now()->subDays(7))) {
                continue;
            }

            $user->notify(new LoyaltyPointsExpiring(
                points: (int) $wallet->loyalty_points,
                daysLeft: $days,
            ));

            $wallet->update(['last_expiry_notified_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} loyalty expiry notifications.");

        return Command::SUCCESS;
    }
}
