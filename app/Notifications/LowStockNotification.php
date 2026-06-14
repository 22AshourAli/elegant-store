<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LowStockNotification extends Notification
{
    public function __construct(
        public ProductVariant $variant,
        public int $stockBefore,
        public int $stockAfter,
    ) {}

    public function via($notifiable): array
    {
        return array_values(array_filter([
            'database',
            in_array(config('mail.default'), ['log', 'array', null]) ? null : 'mail',
        ]));
    }

    public function toMail($notifiable): MailMessage
    {
        $product = $this->variant->product;
        $name = $product->name;
        $sku = $this->variant->sku;
        $color = $this->variant->color;
        $size = $this->variant->size;

        return (new MailMessage)
            ->subject(__('تنبيه: مخزون منخفض - :name', ['name' => $name]))
            ->greeting(__('مرحباً'))
            ->line(__('المخزون أصبح منخفضاً لأحد المنتجات في المتجر.'))
            ->line(__('المنتج: :name', ['name' => $name]))
            ->line($color ? __('اللون: :color', ['color' => $color]) : '')
            ->line($size ? __('المقاس: :size', ['size' => $size]) : '')
            ->line($sku ? __('الكود: :sku', ['sku' => $sku]) : '')
            ->line(__('المخزون المتبقي: :stock', ['stock' => $this->stockAfter]))
            ->action(__('عرض المنتج'), route('admin.products.edit', $product->id));
    }

    public function toDatabase($notifiable): array
    {
        $product = $this->variant->product;
        return [
            'variant_id' => $this->variant->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock_before' => $this->stockBefore,
            'stock_after' => $this->stockAfter,
            'message' => __('مخزون :name منخفض (:stock متبقي)', [
                'name' => $product->name,
                'stock' => $this->stockAfter,
            ]),
        ];
    }
}
