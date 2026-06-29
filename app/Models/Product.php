<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Produit du catalogue gaming.
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'reference',
        'barcode',
        'name',
        'description',
        'brand',
        'purchase_price',
        'sale_price',
        'supplier_sale_price',
        'stock_quantity',
        'minimum_stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'supplier_sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Accesseurs ──────────────────────────────────────────

    protected function margin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sale_price - $this->purchase_price,
        );
    }

    protected function marginRate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->purchase_price <= 0) {
                    return 0;
                }

                return round((($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
            },
        );
    }

    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->stock_quantity <= 0) {
                    return 'out_of_stock';
                }

                if ($this->stock_quantity <= $this->minimum_stock) {
                    return 'low_stock';
                }

                return 'in_stock';
            },
        );
    }

    protected function stockStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->stock_status) {
                'out_of_stock' => 'Rupture',
                'low_stock' => 'Stock faible',
                default => 'En stock',
            },
        );
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%");
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['brand'] ?? null, fn ($q, $brand) => $q->where('brand', $brand))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(($filters['stock_status'] ?? null) === 'low', fn ($q) => $q->lowStock())
            ->when(($filters['stock_status'] ?? null) === 'out', fn ($q) => $q->outOfStock());
    }

    // ─── Méthodes métier ─────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}
