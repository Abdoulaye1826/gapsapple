<?php

namespace App\Models;

use App\Enums\SaleStatus;
use App\Enums\SaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Vente (transaction commerciale).
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'customer_id',
        'user_id',
        'sale_date',
        'sold_at',
        'sale_type',
        'discount_amount',
        'subtotal_ht',
        'total_ttc',
        'status',
        'notes',
        'exchange_voucher_number',
        'exchange_details',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sold_at' => 'datetime',
        'sale_type' => SaleType::class,
        'discount_amount' => 'decimal:2',
        'subtotal_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'exchange_details' => 'array',
        'status' => SaleStatus::class,
    ];

    // ─── Relations ───────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /** IMEI vendus via cette vente. */
    public function soldImeis(): HasMany
    {
        return $this->hasMany(ProductImei::class, 'sale_id');
    }

    /** IMEI reçus en stock via cette vente d'échange. */
    public function receivedImeis(): HasMany
    {
        return $this->hasMany(ProductImei::class, 'exchange_sale_id');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeValidated($query)
    {
        return $query->where('status', SaleStatus::Validated);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', SaleStatus::Draft);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', SaleStatus::Cancelled);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('sale_date', $date);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('sale_date', $year)
            ->whereMonth('sale_date', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('sale_date', $year);
    }

    // ─── Méthodes métier ─────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === SaleStatus::Draft;
    }

    public function isValidated(): bool
    {
        return $this->status === SaleStatus::Validated;
    }

    public function isCancelled(): bool
    {
        return $this->status === SaleStatus::Cancelled;
    }

    public function isVente(): bool
    {
        return $this->sale_type === SaleType::Vente;
    }

    public function isEchange(): bool
    {
        return $this->sale_type === SaleType::Echange;
    }
}
