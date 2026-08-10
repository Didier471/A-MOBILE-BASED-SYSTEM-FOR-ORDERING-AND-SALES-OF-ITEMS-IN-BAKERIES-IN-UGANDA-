<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json(
            Payment::with(['sale', 'user'])
                ->latest()
                ->paginate(10)
        );
    }

    public function store(StorePaymentRequest $request)
    {
        DB::beginTransaction();

        try {
            $sale = Sale::findOrFail($request->sale_id);

            $alreadyPaid = Payment::where('sale_id', $sale->id)
                ->where('status', 'completed')
                ->sum('amount');

            $remainingBalance = $sale->grand_total - $alreadyPaid;

            if ($request->amount > $remainingBalance) {
                return response()->json([
                    'message' => 'Payment exceeds the outstanding balance.',
                    'sale_total' => $sale->grand_total,
                    'already_paid' => $alreadyPaid,
                    'remaining_balance' => $remainingBalance
                ], 422);
            }

            $payment = Payment::create([
                'sale_id' => $sale->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'status' => $request->status ?? 'completed',
                'transaction_reference' => $request->transaction_reference,
                'paid_at' => $request->paid_at ?? now(),
                'created_by' => auth()->id(),
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment recorded successfully.',
                'data' => $payment->load(['sale', 'user'])
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to record payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Payment $payment)
    {
        return response()->json(
            $payment->load(['sale', 'user'])
        );
    }

    public function update(
        UpdatePaymentRequest $request,
        Payment $payment
    ) {
        $payment->update($request->validated());

        return response()->json([
            'message' => 'Payment updated successfully.',
            'data' => $payment->load(['sale', 'user'])
        ]);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.'
        ]);
    }
}