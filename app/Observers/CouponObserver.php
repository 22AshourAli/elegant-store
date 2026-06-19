<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Coupon;
use Illuminate\Support\Facades\Request;

class CouponObserver
{
    private function log(Coupon $coupon, string $action, ?string $description = null, ?array $old = null, ?array $new = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'coupon',
            'subject_type' => Coupon::class,
            'subject_id' => $coupon->id,
            'description' => $description ?? __('global.activity_coupon_' . $action, ['code' => $coupon->code]),
            'ip_address' => Request::ip(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public function created(Coupon $coupon): void
    {
        $this->log($coupon, 'created', null, null, $coupon->toArray());
    }

    public function updated(Coupon $coupon): void
    {
        $old = $coupon->getOriginal();
        $changes = $coupon->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $changedKeys = array_keys($changes);
        $desc = __('global.activity_coupon_updated', ['code' => $coupon->code]) . ' (' . implode(', ', $changedKeys) . ')';

        $this->log($coupon, 'updated', $desc, $old, $changes);
    }

    public function deleted(Coupon $coupon): void
    {
        $this->log($coupon, 'deleted');
    }
}
