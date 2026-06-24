<?php

namespace App\Models;

use App\Enums\RoleSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Utilisateur du Système (authentification + rôle).
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithRole($query, RoleSlug|string $slug)
    {
        $value = $slug instanceof RoleSlug ? $slug->value : $slug;

        return $query->whereHas('role', fn ($q) => $q->where('slug', $value));
    }

    // ─── Méthodes rôle ───────────────────────────────────────

    public function hasRole(RoleSlug|string $slug): bool
    {
        $value = $slug instanceof RoleSlug ? $slug->value : $slug;

        return $this->role?->slug?->value === $value;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleSlug::Admin);
    }

    public function isManager(): bool
    {
        return $this->hasRole(RoleSlug::Manager);
    }

    public function isCashier(): bool
    {
        return $this->hasRole(RoleSlug::Cashier);
    }
}
