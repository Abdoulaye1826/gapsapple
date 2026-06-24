<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'customer_id']);
        $invoices = $this->invoiceService->paginate($filters);
        $summary = $this->invoiceService->summary();

        return view('invoices.index', compact('invoices', 'filters', 'summary'));
    }

    public function create(): View
    {
        return view('invoices.create', [
            'customers' => $this->invoiceService->getCustomers(),
            'sales' => $this->invoiceService->getAvailableSales(),
            'invoice' => null,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->invoiceService->create($request->validated());

        return redirect()->route('invoices.index')
            ->with('success', 'Facture créée avec succès.');
    }

    public function edit(Invoice $invoice): View
    {
        return view('invoices.edit', [
            'invoice' => $invoice,
            'customers' => $this->invoiceService->getCustomers(),
            'sales' => $this->invoiceService->getAvailableSales($invoice->sale),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->update($invoice, $request->validated());

        return redirect()->route('invoices.index')
            ->with('success', 'Facture mise à jour avec succès.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        try {
            $this->invoiceService->delete($invoice);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    public function print(Invoice $invoice): View
    {
        return view('invoices.print', compact('invoice'));
    }

    public function download(Invoice $invoice): Response
    {
        $pdf = PDF::loadView('invoices.print', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true);

        $fileName = "{$invoice->invoice_number}.pdf";
        $content = $pdf->output();

        return response($content, 200, [
            'Content-Type' => 'application/pdf; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function sendWhatsApp(Invoice $invoice): RedirectResponse
    {
        $phone = preg_replace('/[^0-9+]/', '', $invoice->customer?->phone ?? '');

        if (empty($phone)) {
            return back()->with('error', 'Le client n’a pas de numéro WhatsApp.');
        }

        $invoiceUrl = route('invoices.print', $invoice, true);
        $message = rawurlencode("Bonjour, voici votre facture {$invoice->invoice_number} : {$invoiceUrl}");
        $url = "https://api.whatsapp.com/send?phone={$phone}&text={$message}";

        return redirect($url);
    }
}
