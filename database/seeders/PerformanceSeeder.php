<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PerformanceSeeder extends Seeder
{
    private array $colors = [
        'أبيض', 'أسود', 'كحلي', 'رمادي', 'بني', 'أخضر', 'أزرق', 'أحمر', 'بيج', 'نود',
    ];
    private array $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
    private array $productPrefixes = [
        'تيشيرت', 'قميص', 'بنطلون', 'جاكيت', 'فستان', 'بلوزة', 'شورت', 'سوكيت', 'كاب', 'حذاء',
    ];
    private array $productSuffixes = [
        'كلاسيك', 'سبورت', 'كاجوال', 'أنيق', 'فخّم', 'عصري', 'رسمي', ' Onci', 'كومفورت', 'بريميوم',
    ];

    public function run(): void
    {
        $start = microtime(true);

        $branchIds = Branch::pluck('id')->toArray();
        $categoryIds = Category::whereNull('parent_id')->pluck('id')->toArray();

        if (empty($categoryIds)) {
            $this->command?->error('No parent categories found. Run CategorySeeder first.');
            return;
        }

        $this->command?->info('Generating performance test data...');
        $this->command?->info('Branches: ' . count($branchIds) . ', Categories: ' . count($categoryIds));

        $this->command?->info('Creating 5,000 products with variants...');
        $this->createProducts($categoryIds, $branchIds);

        $this->command?->info('Creating 10,000 customers...');
        $this->createCustomers();

        $this->command?->info('Creating 20,000 orders with items...');
        $this->createOrders($branchIds);

        $elapsed = round(microtime(true) - $start, 2);
        $this->command?->info("Performance data seeded in {$elapsed}s");
        $this->command?->info('Products: ' . Product::count() . ', Variants: ' . ProductVariant::count());
        $this->command?->info('Users: ' . User::count() . ', Orders: ' . Order::count() . ', OrderItems: ' . OrderItem::count());
    }

    private function createProducts(array $categoryIds, array $branchIds): void
    {
        $totalProducts = 5000;
        $batchSize = 500;
        $allVariantData = [];
        $allPivotData = [];

        for ($i = 0; $i < $totalProducts; $i += $batchSize) {
            $products = [];
            $batch = min($batchSize, $totalProducts - $i);

            for ($j = 0; $j < $batch; $j++) {
                $idx = $i + $j;
                $prefix = $this->productPrefixes[$idx % count($this->productPrefixes)];
                $suffix = $this->productSuffixes[$idx % count($this->productSuffixes)];
                $name = "{$prefix} {$suffix} #" . ($idx + 1);
                $price = rand(100, 2000) / 10;
                $onSale = $idx % 3 === 0;

                $products[] = [
                    'category_id' => $categoryIds[array_rand($categoryIds)],
                    'name' => $name,
                    'slug' => 'product-' . Str::random(8) . '-' . $idx,
                    'description' => "Description for {$name}",
                    'base_price' => $price,
                    'sale_price' => $onSale ? round($price * 0.8, 2) : null,
                    'discount_start' => $onSale ? now() : null,
                    'discount_end' => $onSale ? now()->addDays(30) : null,
                    'has_variants' => true,
                    'is_active' => true,
                    'featured' => $idx % 20 === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Product::insert($products);
            $productIds = Product::orderByDesc('id')->limit($batch)->pluck('id')->toArray();

            foreach ($productIds as $pId) {
                $colorCount = rand(1, 3);
                $chosenColors = array_rand(array_flip($this->colors), $colorCount);
                if (!is_array($chosenColors)) $chosenColors = [$chosenColors];

                foreach ($chosenColors as $color) {
                    $sizeCount = rand(1, 4);
                    $chosenSizes = array_rand(array_flip($this->sizes), $sizeCount);
                    if (!is_array($chosenSizes)) $chosenSizes = [$chosenSizes];

                    foreach ($chosenSizes as $size) {
                        $allVariantData[] = [
                            'product_id' => $pId,
                            'sku' => "SKU-{$pId}-{$color}-{$size}",
                            'color' => $color,
                            'size' => $size,
                            'price_override' => null,
                            'is_active' => true,
                            'is_default' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            $this->command?->getOutput()->write("  Products: {$i}/{$totalProducts}\r");
        }

        $this->command?->info("  Inserting " . count($allVariantData) . " variants...");

        $maxVariantId = \DB::table('product_variants')->max('id') ?? 0;

        $variantChunks = array_chunk($allVariantData, 500);
        foreach ($variantChunks as $chunk) {
            \DB::table('product_variants')->insert($chunk);
        }

        $newVariants = \DB::table('product_variants')
            ->where('id', '>', $maxVariantId)
            ->select('id')
            ->get()
            ->toArray();

        foreach ($newVariants as $v) {
            $branchId = $branchIds[array_rand($branchIds)];
            $allPivotData[] = [
                'branch_id' => $branchId,
                'product_variant_id' => $v->id,
                'stock' => rand(0, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $pivotChunks = array_chunk($allPivotData, 500);
        foreach ($pivotChunks as $chunk) {
            \DB::table('branch_product_variant')->insert($chunk);
        }
    }

    private function createCustomers(): void
    {
        $total = 10000;
        $batchSize = 500;
        $defaultPassword = Hash::make('password');

        for ($i = 0; $i < $total; $i += $batchSize) {
            $users = [];
            $batch = min($batchSize, $total - $i);

            for ($j = 0; $j < $batch; $j++) {
                $idx = $i + $j;
                $users[] = [
                    'name' => fake()->name(),
                    'email' => "user_{$idx}_" . Str::random(6) . "@test.com",
                    'phone' => '010' . str_pad($idx, 8, '0', STR_PAD_LEFT),
                    'password' => $defaultPassword,
                    'role' => UserRole::Customer->value,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            User::insert($users);
            $this->command?->getOutput()->write("  Customers: {$i}/{$total}\r");
        }
    }

    private function createOrders(array $branchIds): void
    {
        $userIds = User::where('role', UserRole::Customer->value)->pluck('id')->toArray();
        $allVariants = ProductVariant::where('is_active', true)
            ->select('id', 'product_id')
            ->get()
            ->toArray();

        if (empty($userIds) || empty($allVariants)) {
            $this->command?->error('No users or variants found.');
            return;
        }

        $totalOrders = 20000;
        $batchSize = 500;
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
        $paymentMethods = ['cash', 'card', 'wallet'];
        $paymentStatuses = ['paid', 'unpaid', 'refunded'];

        for ($i = 0; $i < $totalOrders; $i += $batchSize) {
            $orders = [];
            $orderItems = [];
            $batch = min($batchSize, $totalOrders - $i);

            for ($j = 0; $j < $batch; $j++) {
                $idx = $i + $j;
                $userId = $userIds[array_rand($userIds)];
                $branchId = $branchIds[array_rand($branchIds)];
                $status = $statuses[array_rand($statuses)];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                $shippingCost = rand(0, 100) / 10;
                $itemCount = rand(1, 5);
                $subtotal = 0;

                $orderItemsData = [];
                $shuffledVariants = $allVariants;
                shuffle($shuffledVariants);
                $selectedVariants = array_slice($shuffledVariants, 0, $itemCount);

                foreach ($selectedVariants as $v) {
                    $qty = rand(1, 3);
                    $unitPrice = rand(50, 2000) / 10;
                    $lineTotal = $unitPrice * $qty;
                    $subtotal += $lineTotal;

                    $orderItemsData[] = [
                        'product_variant_id' => $v['id'],
                        'product_name' => 'Product #' . $v['product_id'],
                        'color' => $this->colors[array_rand($this->colors)],
                        'size' => $this->sizes[array_rand($this->sizes)],
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total' => $lineTotal,
                    ];
                }

                $total = $subtotal + $shippingCost;
                $createdAt = now()->subDays(rand(0, 90))->subHours(rand(0, 23));

                $orders[] = [
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'order_type' => $idx % 10 === 0 ? 'pos' : 'online',
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'subtotal' => round($subtotal, 2),
                    'discount' => 0,
                    'shipping_cost' => $shippingCost,
                    'total' => round($total, 2),
                    'phone' => '010' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'notes' => null,
                    'tracking_number' => 'TRK-' . strtoupper(Str::random(8)),
                    'shipping_status' => $status,
                    'delivered_at' => $status === 'delivered' ? $createdAt->copy()->addDays(rand(1, 5)) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $orderIndex = $idx;
                foreach ($orderItemsData as &$item) {
                    $item['created_at'] = $createdAt;
                    $item['updated_at'] = $createdAt;
                }
                unset($item);

                $orderItems[$orderIndex] = $orderItemsData;
            }

            Order::insert($orders);

            $insertedOrders = Order::orderByDesc('id')->limit($batch)->pluck('id')->toArray();
            sort($insertedOrders);

            $allItemsToInsert = [];
            foreach ($insertedOrders as $k => $oId) {
                $items = $orderItems[$i + $k] ?? [];
                foreach ($items as &$item) {
                    $item['order_id'] = $oId;
                }
                unset($item);
                $allItemsToInsert = array_merge($allItemsToInsert, $items);
            }

            $itemChunks = array_chunk($allItemsToInsert, 500);
            foreach ($itemChunks as $chunk) {
                OrderItem::insert($chunk);
            }

            $this->command?->getOutput()->write("  Orders: {$i}/{$totalOrders}\r");
        }
    }
}
