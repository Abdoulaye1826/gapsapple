<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    /**
     * Recherche de produits pour l'autocomplétion du module d'échange.
     */
    public function searchExchangeProducts(Request $request): JsonResponse
    {
        $term = $request->input('q', '');

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'reference', 'name', 'brand', 'sale_price', 'category_id']);

        return response()->json($products);
    }

    /**
     * Création rapide d'un produit depuis la modale d'échange.
     */
    public function storeExchangeProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'reference' => ['nullable', 'string', 'max:50', 'unique:products,reference'],
            'brand' => ['nullable', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        if (empty($validated['reference'])) {
            $reference = Str::upper('EX-' . Str::random(6));
            while (Product::where('reference', $reference)->exists()) {
                $reference = Str::upper('EX-' . Str::random(6));
            }
            $validated['reference'] = $reference;
        }

        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;
        $validated['minimum_stock'] = 5;
        $validated['is_active'] = true;

        $product = Product::create($validated);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'reference' => $product->reference,
            'brand' => $product->brand,
            'sale_price' => $product->sale_price,
            'category_id' => $product->category_id,
            'stock_quantity' => $product->stock_quantity,
        ], 201);
    }
}
