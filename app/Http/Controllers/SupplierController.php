<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $supplierService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'is_active']);
        $suppliers = $this->supplierService->paginate($filters);
        $summary = $this->supplierService->summary();

        return view('suppliers.index', compact('suppliers', 'filters', 'summary'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->supplierService->create($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur créé avec succès.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->supplierService->update($supplier, $request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        try {
            $this->supplierService->delete($supplier);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur supprimé avec succès.');
    }
}
