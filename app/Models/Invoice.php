<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Facture générée à partir d'une vente validée.
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sale_id',
        'customer_id',
        'issued_at',
        'subtotal_ht',
        'total_ttc',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'subtotal_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'status' => InvoiceStatus::class,
    ];

    // ─── Relations ───────────────────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Attributs calculés ──────────────────────────────────

    protected function amountPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->payments->sum('amount'),
        );
    }

    protected function remainingAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, (float) $this->total_ttc - $this->amount_paid),
        );
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeIssued($query)
    {
        return $query->where('status', InvoiceStatus::Issued);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('issued_at', $year)
            ->whereMonth('issued_at', $month);
    }

    // ─── Méthodes métier ─────────────────────────────────────

    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0.01;
    }
}
