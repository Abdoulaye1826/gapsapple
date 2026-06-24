<?php

namespace Database\Factories;

use App\Enums\RoleSlug;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
            'slug' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'permissions' => [],
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'name' => RoleSlug::Admin->label(),
            'slug' => RoleSlug::Admin->value,
            'description' => 'Accès total au Système',
            'permissions' => ['*'],
        ]);
    }
}
