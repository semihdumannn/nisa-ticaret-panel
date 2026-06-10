<?php

namespace App\Models;

use App\Models\Order;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, MassPrunable, Notifiable, SoftDeletes;

    protected $fillable = [
        'firebase_uid',
        'phone',
        'name',
        'email',
        'role',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'password',
        'totp_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'totp_secret'       => 'encrypted',
        ];
    }

    // ── Activity Log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'role', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}");
    }

    // ── Filament Access ───────────────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole(\Illuminate\Database\Eloquent\Builder $query, string $role): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('role', $role);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    // ── Pruning ───────────────────────────────────────────────────────────────

    /**
     * Hard-delete soft-deleted non-admin users older than 1 year.
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::onlyTrashed()
            ->where('role', '!=', 'admin')
            ->where('deleted_at', '<=', now()->subYear());
    }
}
