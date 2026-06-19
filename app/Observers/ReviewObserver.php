<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Review;
use Illuminate\Support\Facades\Request;

class ReviewObserver
{
    private function log(Review $review, string $action, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'review',
            'subject_type' => Review::class,
            'subject_id' => $review->id,
            'description' => $description ?? __('global.activity_review_' . $action, ['id' => $review->id]),
            'ip_address' => Request::ip(),
        ]);
    }

    public function updated(Review $review): void
    {
        if ($review->wasChanged('status')) {
            $action = $review->status === 'approved' ? 'approved' : 'rejected';
            $this->log($review, $action);
        }
    }
}
