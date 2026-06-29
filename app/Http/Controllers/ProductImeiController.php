<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductImeiRequest;
use App\Models\Product;
use App\Models\ProductImei;
use App\Services\ProductImeiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImeiController extends Controller
{
    public function __construct(private readonly ProductImeiService $productImeiService)
    {
    }

    public function store(StoreProductImeiRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        try {
            $created = $this->productImeiService->store($product, $request->validated()['imeis']);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'imeis' => $created,
                'stock_quantity' => $product->fresh()->stock_quantity,
            ]);
        }

        return back()->with('success', count($created) . ' IMEI ajouté(s) avec succès.');
    }

    public function destroy(Request $request, ProductImei $imei): RedirectResponse|JsonResponse
    {
        $product = $imei->product;

        try {
            $this->productImeiService->destroy($imei);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['stock_quantity' => $product->fresh()->stock_quantity]);
        }

        return back()->with('success', 'IMEI supprimé avec succès.');
    }

    /**
     * Liste des IMEI disponibles d'un produit, utilisée par le formulaire de
     * vente (sélection / scan de l'IMEI à vendre).
     */
    public function available(Product $product): JsonResponse
    {
        $imeis = $product->imeis()->available()->orderBy('imei')->pluck('imei');

        return response()->json($imeis);
    }
}
