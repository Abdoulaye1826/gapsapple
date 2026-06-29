<?php

namespace App\Services;

use App\Enums\ImeiStatus;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des retours client : liste les produits vendus et permet, en un
 * clic, de remettre le produit en stock et de déduire son montant du
 * chiffre d'affaires (total de la vente et de la facture associée).
 */
class ReturnService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SaleItem::query()
            ->with(['sale.customer', 'product', 'returnedBy'])
            ->whereHas('sale', fn ($q) => $q->where('status', SaleStatus::Validated))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%")
                            ->orWhere('reference', 'like', "%{$search}%"))
                        ->orWhereHas('sale', fn ($q) => $q->where('sale_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($q2) => $q2->where('full_name', 'like', "%{$search}%")));
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'returned') {
                    $query->returned();
                } elseif ($status === 'not_returned') {
                    $query->notReturned();
                }
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Retourne la ligne de vente : remet le produit en stock, journalise le
     * mouvement, et déduit le montant de la ligne du total de la vente (et
     * de la facture associée si elle existe).
     */
    public function returnItem(SaleItem $item, int $userId): SaleItem
    {
        if ($item->isReturned()) {
            throw new \RuntimeException('Ce produit a déjà été retourné.');
        }

        $sale = $item->sale;
        if ($sale === null || $sale->status !== SaleStatus::Validated) {
            throw new \RuntimeException('Seuls les produits issus d\'une vente validée peuvent être retournés.');
        }

        return DB::transaction(function () use ($item, $sale, $userId) {
            $product = $item->product;

            if ($product !== null && $product->tracks_imei) {
                // Le téléphone redevient disponible et sort de la fiche de la
                // vente : le stock est recalculé à partir des IMEI restants,
                // jamais par simple arithmétique.
                $imei = $item->productImei;
                $quantityBefore = $product->stock_quantity;

                if ($imei !== null) {
                    $imei->update([
                        'status' => ImeiStatus::Available->value,
                        'sale_id' => null,
                        'sold_at' => null,
                    ]);
                }

                $product->syncImeiStock();

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'type' => StockMovementType::Return,
                    'quantity' => 1,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $product->fresh()->stock_quantity,
                    'reason' => 'Retour client' . ($imei ? " (IMEI {$imei->imei})" : ''),
                    'reference' => $sale->sale_number,
                ]);
            } elseif ($product !== null) {
                $quantityBefore = $product->stock_quantity;
                $quantityAfter = $quantityBefore + $item->quantity;

                $product->update(['stock_quantity' => $quantityAfter]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'type' => StockMovementType::Return,
                    'quantity' => $item->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'reason' => 'Retour client',
                    'reference' => $sale->sale_number,
                ]);
            }

            $newSaleTotal = max(0, (float) $sale->total_ttc - (float) $item->line_total);
            $sale->update(['total_ttc' => $newSaleTotal]);

            $invoice = $sale->invoice;
            if ($invoice !== null) {
                $invoice->update(['total_ttc' => $newSaleTotal]);
            }

            $item->update([
                'returned_at' => now(),
                'returned_by' => $userId,
            ]);

            $this->activityLog->log(
                'return',
                $item,
                "Retour produit : {$product?->name} (vente {$sale->sale_number}), -" . number_format($item->line_total, 0, ',', ' ') . ' FCFA du CA'
            );

            return $item->fresh();
        });
    }
}
