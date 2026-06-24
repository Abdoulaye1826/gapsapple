<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seed des 4 rôles Système avec leurs permissions.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => RoleSlug::Admin->label(),
                'slug' => RoleSlug::Admin->value,
                'description' => 'Gestion produits, stock, ventes et rapports',
                'permissions' => [
                    'products.manage', 'categories.manage', 'stock.manage',
                    'suppliers.manage', 'sales.manage', 'dashboard.view',
                ],
            ],
            [
                'name' => RoleSlug::Manager->label(),
                'slug' => RoleSlug::Manager->value,
                'description' => 'Gestion produits, stock, ventes et rapports',
                'permissions' => [
                    'products.manage', 'categories.manage', 'stock.manage',
                    'suppliers.manage', 'sales.manage', 'dashboard.view',
                ],
            ],
            [
                'name' => RoleSlug::Cashier->label(),
                'slug' => RoleSlug::Cashier->value,
                'description' => 'Création ventes, gestion clients et factures',
                'permissions' => [
                    'sales.create', 'sales.validate', 'customers.manage',
                    'invoices.view', 'invoices.generate', 'dashboard.view',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
