<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['search']);
        $customers = $this->customerService->paginate($filters);
        $summary = $this->customerService->summary();

        return view('customers.index', compact('customers', 'filters', 'summary'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'city' => $customer->city,
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Client créé avec succès.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->update($customer, $request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Client mis à jour avec succès.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            $this->customerService->delete($customer);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('customers.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
