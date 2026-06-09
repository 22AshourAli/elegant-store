<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentSettlement;
use Illuminate\Support\Facades\DB;

class PaymentReconciliationService
{
    /**
     * Default commission structure per gateway.
     * In production, these are fetched from config or the gateway API.
     */
    const GATEWAY_FEES = [
        'paymob'   => ['percentage' => 2.75, 'fixed' => 3.0],
        'fawry'    => ['percentage' => 1.75, 'fixed' => 0.0],
        'credit'   => ['percentage' => 2.75, 'fixed' => 0.0],
        'cash'     => ['percentage' => 0.0,  'fixed' => 0.0],
        'wallet'   => ['percentage' => 0.0,  'fixed' => 0.0],
    ];

    /**
     * Calculate gateway fee and net amount for a payment.
     */
    public function calculateNetAmount(float $grossAmount, string $gateway = 'paymob'): array
    {
        $fees = self::GATEWAY_FEES[$gateway] ?? self::GATEWAY_FEES['paymob'];

        $feeAmount = ($grossAmount * $fees['percentage'] / 100) + $fees['fixed'];
        $netAmount = $grossAmount - $feeAmount;

        return [
            'gross_amount' => round($grossAmount, 2),
            'gateway_fee'  => round($feeAmount, 2),
            'net_amount'   => round(max($netAmount, 0), 2),
        ];
    }

    /**
     * Update a payment record with fee/net calculations.
     */
    public function reconcilePayment(int $paymentId): Payment
    {
        $payment = Payment::findOrFail($paymentId);

        $calc = $this->calculateNetAmount((float) $payment->amount, $payment->gateway);

        $payment->update([
            'gateway_fee'  => $calc['gateway_fee'],
            'net_amount'   => $calc['net_amount'],
            'gateway_reference' => $payment->gateway_reference ?? $payment->transaction_id,
        ]);

        return $payment->fresh();
    }

    /**
     * Get the financial summary: gross sales vs net after gateway fees.
     */
    public function financialSummary(string $startDate = null, string $endDate = null): array
    {
        $query = Payment::whereIn('status', ['paid', 'completed', 'captured', 'success']);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $summary = (clone $query)
            ->select(
                DB::raw('SUM(amount) as gross_revenue'),
                DB::raw('SUM(gateway_fee) as total_gateway_fees'),
                DB::raw('SUM(net_amount) as net_revenue'),
            )
            ->first();

        $byGateway = (clone $query)
            ->select('gateway',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as gross_amount'),
                DB::raw('SUM(gateway_fee) as total_fees'),
                DB::raw('SUM(net_amount) as net_amount')
            )
            ->groupBy('gateway')
            ->get();

        return [
            'gross_revenue'      => (float) ($summary?->gross_revenue ?? 0),
            'total_gateway_fees' => (float) ($summary?->total_gateway_fees ?? 0),
            'net_revenue'        => (float) ($summary?->net_revenue ?? 0),
            'fee_percent'        => ($summary?->gross_revenue ?? 0) > 0
                ? round((($summary?->total_gateway_fees ?? 0) / ($summary?->gross_revenue ?? 1)) * 100, 2)
                : 0,
            'by_gateway'         => $byGateway,
        ];
    }

    /**
     * Create a settlement batch record (called when gateway sends payout).
     */
    public function createSettlement(array $data): PaymentSettlement
    {
        return PaymentSettlement::create([
            'batch_id'    => $data['batch_id'],
            'gateway'     => $data['gateway'],
            'gross_amount' => $data['gross_amount'],
            'total_fees'  => $data['total_fees'] ?? 0,
            'net_amount'  => $data['net_amount'],
            'status'      => $data['status'] ?? 'pending',
            'metadata'    => $data['metadata'] ?? null,
            'settled_at'  => $data['settled_at'] ?? null,
        ]);
    }
}
