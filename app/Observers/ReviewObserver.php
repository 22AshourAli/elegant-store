<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Review;
use Illuminate\Support\Facades\Request;

class ReviewObserver
{
    private function log(Review $review, string $action, ?string $description = null, ?array $old = null, ?array $new = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'review',
            'subject_type' => Review::class,
            'subject_id' => $review->id,
            'description' => $description ?? __('global.activity_review_' . $action, ['id' => $review->id]),
            'ip_address' => Request::ip(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public function updated(Review $review): void
    {
        if ($review->wasChanged('status')) {
            $old = $review->getOriginal('status');
            $new = $review->getAttribute('status');
            $action = $new === 'approved' ? 'approved' : 'rejected';
            $this->log($review, $action, null, ['status' => $old], ['status' => $new]);
        }
    }
}
