<?php

namespace App\Services;

use App\Enums\QuoteStatus;
use App\Enums\SaleStatus;
use App\Enums\SaleType;
use App\Enums\WarrantyDuration;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly SaleService $saleService
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Quote::query()
            ->with(['customer', 'items'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('quote_number', 'like', "%{$search}%")
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
            'total' => Quote::count(),
            'draft' => Quote::where('status', QuoteStatus::Draft)->count(),
            'sent' => Quote::where('status', QuoteStatus::Sent)->count(),
            'accepted' => Quote::where('status', QuoteStatus::Accepted)->count(),
            'refused' => Quote::where('status', QuoteStatus::Refused)->count(),
            'converted' => Quote::where('status', QuoteStatus::Converted)->count(),
            'amount_total' => (float) Quote::where('status', '!=', QuoteStatus::Refused->value)->sum('total_ttc'),
        ];
    }

    public function getCustomers()
    {
        return Customer::orderBy('full_name')->get();
    }

    public function getProducts()
    {
        return Product::active()->orderBy('name')->get();
    }

    public function getCategories()
    {
        return Category::active()->orderBy('name')->get();
    }

    public function create(array $data, int $userId): Quote
    {
        $data['user_id'] = $userId;
        $data['quote_number'] = $this->generateQuoteNumber();
        $data['quote_date'] = now()->toDateString();
        $data['status'] = $data['status'] ?? QuoteStatus::Draft;

        $totals = $this->calculateTotals($data);
        $data['subtotal_ht'] = 0;
        $data['total_ttc'] = $totals['total'];

        return DB::transaction(function () use ($data) {
            $quote = Quote::create($data);

            foreach ($this->buildQuoteItems($data) as $itemData) {
                $quote->items()->create($itemData);
            }

            $this->activityLog->log('create', $quote, "Devis créé : {$quote->quote_number}");

            return $quote;
        });
    }

    public function update(Quote $quote, array $data): Quote
    {
        if ($quote->status === QuoteStatus::Converted) {
            throw new \RuntimeException('Impossible de modifier un devis déjà converti en vente.');
        }

        $totals = $this->calculateTotals($data);
        $data['subtotal_ht'] = 0;
        $data['total_ttc'] = $totals['total'];

        return DB::transaction(function () use ($quote, $data) {
            $quote->update($data);

            $quote->items()->delete();
            foreach ($this->buildQuoteItems($data) as $itemData) {
                $quote->items()->create($itemData);
            }

            $this->activityLog->log('update', $quote, "Devis mis à jour : {$quote->quote_number}");

            return $quote->fresh();
        });
    }

    public function delete(Quote $quote): void
    {
        if ($quote->status === QuoteStatus::Converted) {
            throw new \RuntimeException('Impossible de supprimer un devis déjà converti en vente.');
        }

        $quoteNumber = $quote->quote_number;
        $quote->delete();

        $this->activityLog->log('delete', null, "Devis supprimé : {$quoteNumber}");
    }

    /**
     * Convertit un devis accepté en vente (brouillon, sans impact stock
     * immédiat) en conservant exactement les prix négociés dans le devis —
     * pas les prix catalogue actuels, qui ont pu changer depuis. La
     * finalisation (mode de paiement, garantie, validation) se fait ensuite
     * via le formulaire de vente habituel.
     */
    public function convertToSale(Quote $quote, int $userId): Sale
    {
        if ($quote->status !== QuoteStatus::Accepted) {
            throw new \RuntimeException('Seul un devis accepté peut être converti en vente.');
        }

        $quote->loadMissing('items.product');

        return DB::transaction(function () use ($quote, $userId) {
            $saleDate = now()->toDateString();
            $warrantyDuration = WarrantyDuration::Days30;

            $sale = Sale::create([
                'sale_number' => $this->saleService->generateSaleNumber(SaleType::Vente),
                'customer_id' => $quote->customer_id,
                'user_id' => $userId,
                'sale_date' => $saleDate,
                'sold_at' => now(),
                'sale_type' => SaleType::Vente,
                'discount_amount' => $quote->discount_amount,
                'subtotal_ht' => 0,
                'total_ttc' => $quote->total_ttc,
                'status' => SaleStatus::Draft,
                'notes' => "Issu du devis {$quote->quote_number}.",
                'warranty_duration' => $warrantyDuration,
                'warranty_end_date' => $warrantyDuration->endDateFrom(\Carbon\Carbon::parse($saleDate)),
            ]);

            foreach ($quote->items as $item) {
                $sale->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => 0,
                    'line_total' => $item->line_total,
                ]);
            }

            $quote->update([
                'status' => QuoteStatus::Converted,
                'converted_sale_id' => $sale->id,
            ]);

            $this->activityLog->log('convert', $quote, "Devis {$quote->quote_number} converti en vente {$sale->sale_number}");

            return $sale;
        });
    }

    /**
     * Génère le PDF du devis, utilisé à la fois par le téléchargement direct
     * et par le lien public signé partagé sur WhatsApp (même pattern que
     * InvoiceService::renderPdfContent()).
     */
    public function renderPdfContent(Quote $quote): string
    {
        $quote->loadMissing(['customer', 'items.product']);

        $pdf = PDF::loadView('documents.quote_document', ['quote' => $quote, 'downloadUrl' => null])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultMediaType', 'print');

        return $pdf->output();
    }

    /**
     * Numéro de devis continu (DEV-000001, DEV-000002, ...), jamais
     * réinitialisé par jour, même logique que Sale::generateSaleNumber().
     */
    private function generateQuoteNumber(): string
    {
        $max = Quote::query()
            ->get(['quote_number'])
            ->pluck('quote_number')
            ->filter()
            ->map(function ($value) {
                preg_match('/(\d+)$/', $value, $matches);

                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max();

        return sprintf('DEV-%06d', ((int) $max) + 1);
    }

    private function buildQuoteItems(array $data): array
    {
        $productIds = Arr::wrap($data['product_id'] ?? []);
        $quantities = Arr::wrap($data['quantity'] ?? []);
        $unitPrices = Arr::wrap($data['unit_price'] ?? []);

        $items = [];
        foreach ($productIds as $index => $productId) {
            if (empty($productId)) {
                continue;
            }

            $quantity = isset($quantities[$index]) ? max(1, (int) $quantities[$index]) : 1;
            $unitPrice = isset($unitPrices[$index]) ? (float) $unitPrices[$index] : 0;

            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($quantity * max(0, $unitPrice), 2),
            ];
        }

        return $items;
    }

    private function calculateTotals(array $data): array
    {
        $items = $this->buildQuoteItems($data);
        $total = array_sum(array_column($items, 'line_total'));
        $discount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0;

        return [
            'total' => max(0, $total - $discount),
        ];
    }
}
