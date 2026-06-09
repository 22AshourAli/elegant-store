<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ---------------------------------------------------------------
         * ADVANCED 1: Return Analytics — item-level return tracking
         * ---------------------------------------------------------------
         * Each return request can have multiple items, each linked to an
         * order_item so we can analyse which product/color/size is most
         * frequently returned and why.
         */
        Schema::create('return_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('reason_category')->nullable()
                ->comment('size_too_small|size_too_large|defect|not_as_described|changed_mind|other');
            $table->text('reason_detail')->nullable();
            $table->timestamps();

            $table->index(['return_request_id', 'order_item_id']);
        });

        // Add a JSON column to return_requests for aggregated analytics
        Schema::table('return_requests', function (Blueprint $table) {
            $table->json('analytics')->nullable()->after('exchange_data')
                ->comment('Cached breakdown: most-returned sizes, colors, reasons');
        });

        /*
         * ---------------------------------------------------------------
         * ADVANCED 4: Abandoned Cart Funnel — track checkout abandonment
         * ---------------------------------------------------------------
         * We add a column to abandoned_carts so we know at which step the
         * user dropped off (cart_viewed / shipping_step / payment_step /
         * review_step).
         */
        Schema::table('abandoned_carts', function (Blueprint $table) {
            $table->string('checkout_step')->nullable()->after('coupon_code')
                ->comment('cart_viewed|shipping_step|payment_step|review_step');
        });

        /*
         * ---------------------------------------------------------------
         * ADVANCED 5: Payment Gateway Reconciliation
         * ---------------------------------------------------------------
         * We augment the payments table with fee / net / settlement fields
         * so the admin can see the real net revenue after gateway commission.
         */
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('gateway_fee', 10, 2)->default(0)->after('amount')
                ->comment('Fee charged by the payment gateway');
            $table->decimal('net_amount', 10, 2)->default(0)->after('gateway_fee')
                ->comment('amount - gateway_fee = actual deposit');
            $table->string('settlement_status')->default('pending')->after('net_amount')
                ->comment('pending|settled|failed');
            $table->timestamp('settled_at')->nullable()->after('settlement_status');
            $table->string('gateway_reference')->nullable()->after('transaction_id')
                ->comment('Transaction reference from the payment gateway');
        });

        /*
         * ---------------------------------------------------------------
         * ADVANCED 5 (cont): Settlement records
         * ---------------------------------------------------------------
         * A separate table for bank / wallet settlement batches so the
         * admin can reconcile daily/weekly payouts.
         */
        Schema::create('payment_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique()->comment('Payout batch reference');
            $table->string('gateway');
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('total_fees', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('status')->default('pending')
                ->comment('pending|completed|failed');
            $table->json('metadata')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        /*
         * ---------------------------------------------------------------
         * Performance indexes for analytics queries
         * ---------------------------------------------------------------
         */
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['product_variant_id', 'created_at'],
                          'oi_variant_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_items');
        Schema::dropIfExists('payment_settlements');

        Schema::table('return_requests', fn(Blueprint $t) => $t->dropColumn('analytics'));
        Schema::table('abandoned_carts', fn(Blueprint $t) => $t->dropColumn('checkout_step'));
        Schema::table('payments', fn(Blueprint $t) => $t->dropColumn([
            'gateway_fee', 'net_amount', 'settlement_status', 'settled_at', 'gateway_reference',
        ]));
        Schema::table('order_items', fn(Blueprint $t) => $t->dropIndex('oi_variant_created_idx'));
    }
};
