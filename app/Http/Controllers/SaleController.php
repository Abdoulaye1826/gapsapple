<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $saleService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'sale_type', 'customer_id']);
        $sales = $this->saleService->paginate($filters);

        return view('sales.index', compact('sales', 'filters'));
    }

    public function create(): View
    {
        return view('sales.create', [
            'customers' => $this->saleService->getCustomers(),
            'products' => $this->saleService->getProducts(),
            'categories' => $this->saleService->getCategories(),
            'sale' => null,
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $this->saleService->create($request->validated(), auth()->id());

        return redirect()->route('sales.index')
            ->with('success', 'Vente créée avec succès.');
    }

    public function edit(Sale $sale): View
    {
        return view('sales.edit', [
            'sale' => $sale,
            'customers' => $this->saleService->getCustomers(),
            'products' => $this->saleService->getProducts(),
            'categories' => $this->saleService->getCategories(),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $this->saleService->update($sale, $request->validated());

        return redirect()->route('sales.index')
            ->with('success', 'Vente mise à jour avec succès.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        try {
            $this->saleService->delete($sale);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.index')
            ->with('success', 'Vente supprimée avec succès.');
    }
}
