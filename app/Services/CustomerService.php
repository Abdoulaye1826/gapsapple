<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Customer::query()
            ->withCount('invoices')
            ->search($filters['search'] ?? null)
            ->orderBy('full_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(): array
    {
        $total = Customer::count();
        $withInvoices = Customer::has('invoices')->count();

        return [
            'total' => $total,
            'with_invoices' => $withInvoices,
            'without_invoices' => $total - $withInvoices,
        ];
    }

    public function create(array $data): Customer
    {
        $customer = Customer::create($data);

        $this->activityLog->log('create', $customer, "Client créé : {$customer->full_name}");

        return $customer;
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        $this->activityLog->log('update', $customer, "Client modifié : {$customer->full_name}");

        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        if ($customer->sales()->exists() || $customer->invoices()->exists()) {
            throw new \RuntimeException('Impossible de supprimer un client lié à des ventes ou factures.');
        }

        $name = $customer->full_name;
        $customer->delete();

        $this->activityLog->log('delete', null, "Client supprimé : {$name}");
    }
}
