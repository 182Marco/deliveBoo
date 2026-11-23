<?php

namespace App;

use App\Avatar\AvatarCatalog;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'last_name', 'company_name', 'phone', 'email', 'password', 'vat_number'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // not fillable: written only by the platform backoffice (ban) and by seeders / commands (role)
            'banned_at' => 'datetime',
            // not fillable: written only by Admin\AvatarController
            'avatar' => 'array',
        ];
    }

    public const CUSTOMER = 'customer';
    public const RESTAURATEUR = 'restaurateur';
    public const ADMIN = 'admin';
    public const ROLES = [self::CUSTOMER, self::RESTAURATEUR, self::ADMIN];
    // the roles a public form (registration, admin "new account") may choose: never admin
    public const SIGNUP_ROLES = [self::CUSTOMER, self::RESTAURATEUR];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /** admins that can still sign in: the platform must never drop to zero */
    public static function activeAdminCount(): int
    {
        return static::where('role', self::ADMIN)->whereNull('banned_at')->count();
    }

    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'banned_by');
    }

    /** seed for packages/svg-avatar: a saved random seed, or one derived from the id */
    public function avatarSeed(): string
    {
        return $this->avatar['seed'] ?? 'user-'.$this->id;
    }

    /** traits chosen by the user on top of the seed (only known names and values) */
    public function avatarOverrides(): array
    {
        return AvatarCatalog::sanitizeOverrides($this->avatar['overrides'] ?? []);
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }
}
