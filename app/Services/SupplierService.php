<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Logique métier pour les fournisseurs.
 */
class SupplierService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::query()
            ->search($filters['search'] ?? null)
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', (bool) $filters['is_active']);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(): array
    {
        return [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Supplier> */
    public function activeList()
    {
        return Supplier::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): Supplier
    {
        $supplier = Supplier::create($data);

        $this->activityLog->log('create', $supplier, "Fournisseur créé : {$supplier->name}");

        return $supplier;
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        $this->activityLog->log('update', $supplier, "Fournisseur modifié : {$supplier->name}");

        return $supplier->fresh();
    }

    public function delete(Supplier $supplier): void
    {
        if ($supplier->stockMovements()->exists()) {
            throw new \RuntimeException('Impossible de supprimer un fournisseur lié à des mouvements de stock.');
        }

        $name = $supplier->name;
        $supplier->delete();

        $this->activityLog->log('delete', null, "Fournisseur supprimé : {$name}");
    }
}
