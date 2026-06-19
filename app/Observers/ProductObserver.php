<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Support\Facades\Request;

class ProductObserver
{
    private function log(Product $product, string $action, ?string $description = null, ?array $old = null, ?array $new = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'product',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'description' => $description ?? __('global.activity_product_' . $action, ['id' => $product->id, 'name' => $product->name]),
            'ip_address' => Request::ip(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public function created(Product $product): void
    {
        $this->log($product, 'created');
    }

    public function updated(Product $product): void
    {
        $old = $product->getOriginal();
        $changes = $product->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        if (isset($old['current_price'], $changes['current_price'])) {
            $this->log($product, 'updated', __('global.activity_product_price_updated', [
                'id' => $product->id,
                'name' => $product->name,
                'old' => $old['current_price'],
                'new' => $changes['current_price'],
            ]), ['current_price' => $old['current_price']], ['current_price' => $changes['current_price']]);
        } else {
            $changedKeys = array_keys($changes);
            $desc = __('global.activity_product_updated', ['id' => $product->id, 'name' => $product->name]) . ' (' . implode(', ', $changedKeys) . ')';
            $this->log($product, 'updated', $desc, $old, $changes);
        }
    }

    public function deleted(Product $product): void
    {
        $this->log($product, 'deleted');
    }
}
