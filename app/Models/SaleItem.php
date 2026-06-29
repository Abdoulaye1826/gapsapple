<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne de détail d'une vente.
 */
class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_imei_id',
        'quantity',
        'unit_price',
        'discount',
        'line_total',
        'returned_at',
        'returned_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'returned_at' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productImei(): BelongsTo
    {
        return $this->belongsTo(ProductImei::class);
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_at');
    }

    public function scopeNotReturned($query)
    {
        return $query->whereNull('returned_at');
    }

    // ─── Méthodes métier ─────────────────────────────────────

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }
}
