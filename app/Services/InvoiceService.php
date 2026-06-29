<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['customer', 'sale', 'payments'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('full_name', 'like', "%{$search}%"));
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['customer_id'] ?? null, function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(): array
    {
        return [
            'total' => Invoice::count(),
            'issued' => Invoice::where('status', InvoiceStatus::Issued)->count(),
            'partial' => Invoice::where('status', InvoiceStatus::Partial)->count(),
            'paid' => Invoice::where('status', InvoiceStatus::Paid)->count(),
            'cancelled' => Invoice::where('status', InvoiceStatus::Cancelled)->count(),
        ];
    }

    public function getCustomers()
    {
        return Customer::orderBy('full_name')->get();
    }

    public function getAvailableSales(?Sale $currentSale = null)
    {
        $query = Sale::query()
            ->orderByDesc('id');

        if ($currentSale !== null) {
            $query->where(function ($subQuery) use ($currentSale) {
                $subQuery->whereDoesntHave('invoice')
                         ->orWhere('id', $currentSale->id);
            });
        } else {
            $query->whereDoesntHave('invoice');
        }

        return $query->get();
    }

    public function create(array $data): Invoice
    {
        if (!empty($data['sale_id'])) {
            $sale = Sale::find($data['sale_id']);
            $data['customer_id'] = $sale?->customer_id;
        }

        $data['invoice_number'] = $data['invoice_number'] ?? $this->generateInvoiceNumber();
        $data['status'] = $data['status'] ?? InvoiceStatus::Issued;

        $invoice = Invoice::create($data);

        $this->activityLog->log('create', $invoice, "Facture créée : {$invoice->invoice_number}");

        return $invoice;
    }

    public function createFromSale(Sale $sale): Invoice
    {
        return $this->create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'issued_at' => $sale->sale_date,
            'subtotal_ht' => $sale->subtotal_ht,
            'total_ttc' => $sale->total_ttc,
            'status' => InvoiceStatus::Issued,
            'invoice_number' => $this->generateInvoiceNumberFromSale($sale),
        ]);
    }

    private function generateInvoiceNumberFromSale(Sale $sale): string
    {
        $suffix = preg_replace('/^[A-Z]-/', '', $sale->sale_number);

        return sprintf('F-%s', $suffix);
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        if (!empty($data['sale_id'])) {
            $sale = Sale::find($data['sale_id']);
            $data['customer_id'] = $sale?->customer_id;
        }

        $invoice->update($data);

        $this->activityLog->log('update', $invoice, "Facture modifiée : {$invoice->invoice_number}");

        return $invoice->fresh();
    }

    public function delete(Invoice $invoice): void
    {
        if ($invoice->status === InvoiceStatus::Paid) {
            throw new \RuntimeException('Impossible de supprimer une facture déjà payée.');
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        $this->activityLog->log('delete', null, "Facture supprimée : {$invoiceNumber}");
    }

    /**
     * Numéro de facture continu (F-000001, F-000002, ...), jamais
     * réinitialisé par jour. Basé sur la plus grande valeur numérique déjà
     * utilisée (toutes factures confondues, qu'elles viennent d'une vente
     * ou d'une saisie manuelle) pour ne jamais entrer en collision.
     */
    private function generateInvoiceNumber(): string
    {
        $max = Invoice::query()
            ->get(['invoice_number'])
            ->pluck('invoice_number')
            ->filter()
            ->map(function ($value) {
                // Ne retient que le suffixe numérique final, jamais une
                // éventuelle date intercalée dans l'ancien format.
                preg_match('/(\d+)$/', $value, $matches);

                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max();

        return sprintf('F-%06d', ((int) $max) + 1);
    }
}
