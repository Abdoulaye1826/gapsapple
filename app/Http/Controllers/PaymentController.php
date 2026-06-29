<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        try {
            $this->paymentService->store($invoice, $request->validated(), auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.edit', $invoice)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $invoice = $payment->invoice;

        try {
            $this->paymentService->destroy($payment);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.edit', $invoice)
            ->with('success', 'Paiement supprimé.');
    }
}
