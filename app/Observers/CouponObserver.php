<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Coupon;
use Illuminate\Support\Facades\Request;

class CouponObserver
{
    private function log(Coupon $coupon, string $action, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'coupon',
            'subject_type' => Coupon::class,
            'subject_id' => $coupon->id,
            'description' => $description ?? __('global.activity_coupon_' . $action, ['code' => $coupon->code]),
            'ip_address' => Request::ip(),
        ]);
    }

    public function created(Coupon $coupon): void
    {
        $this->log($coupon, 'created');
    }

    public function updated(Coupon $coupon): void
    {
        $this->log($coupon, 'updated');
    }

    public function deleted(Coupon $coupon): void
    {
        $this->log($coupon, 'deleted');
    }
}
