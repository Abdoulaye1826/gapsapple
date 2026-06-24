<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed des utilisateurs de démonstration (un par rôle).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'role' => RoleSlug::Admin,
                'name' => 'Système',
                'email' => 'admin@gaming-store.local',
                'phone' => '+221 77 000 00 01',
            ],
            [
                'role' => RoleSlug::Manager,
                'name' => 'Moussa Diop',
                'email' => 'gestionnaire@gaming-store.local',
                'phone' => '+221 77 000 00 02',
            ],
            [
                'role' => RoleSlug::Cashier,
                'name' => 'Fatou Sall',
                'email' => 'caissier@gaming-store.local',
                'phone' => '+221 77 000 00 03',
            ],
        ];

        foreach ($users as $data) {
            $role = Role::bySlug($data['role'])->first();

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $role->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

        }
    }
}
