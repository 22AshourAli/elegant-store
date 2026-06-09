<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;

class PaymentReconciliationController extends Controller
{
    public function __construct(
        protected PaymentReconciliationService $reconciliationService
    ) {}

    /**
     * Reconcile a single payment (calculate fee & net).
     */
    public function reconcilePayment(Request $request, int $paymentId)
    {
        $payment = $this->reconciliationService->reconcilePayment($paymentId);

        return response()->json([
            'message' => 'Payment reconciled',
            'payment' => $payment,
        ]);
    }

    /**
     * Batch reconcile un-reconciled payments (fee = 0).
     */
    public function batchReconcile()
    {
        $payments = Payment::where('gateway_fee', 0)
            ->whereIn('status', ['paid', 'completed', 'captured'])
            ->get();

        $count = 0;
        foreach ($payments as $payment) {
            $this->reconciliationService->reconcilePayment($payment->id);
            $count++;
        }

        return response()->json([
            'message' => "{$count} payments reconciled",
            'count' => $count,
        ]);
    }

    /**
     * Financial summary: gross vs net after gateway fees.
     */
    public function financialSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        return response()->json(
            $this->reconciliationService->financialSummary(
                $request->input('start_date'),
                $request->input('end_date')
            )
        );
    }

    /**
     * Create a manual settlement record.
     */
    public function storeSettlement(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string|unique:payment_settlements,batch_id',
            'gateway' => 'required|string',
            'gross_amount' => 'required|numeric|min:0',
            'total_fees' => 'nullable|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,completed,failed',
            'settled_at' => 'nullable|date',
        ]);

        $settlement = $this->reconciliationService->createSettlement($request->all());

        return response()->json($settlement, 201);
    }
}
