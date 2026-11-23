<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One row per relevant action of the platform backoffice. Written only through record().
#[Table('platform_audit_logs')]
#[WithoutTimestamps]
class AuditLog extends Model
{
    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_BANNED = 'user.banned';
    public const USER_UNBANNED = 'user.unbanned';
    public const ADMIN_LOGIN = 'admin.login';

    // meta keys that must never be stored, whatever a caller passes
    private const SECRET_KEYS = ['password', 'password_confirmation', 'remember_token', '_token'];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** append an entry: the actor is the logged-in user, the IP the one of the request */
    public static function record(string $action, ?Model $target = null, array $meta = [], ?User $actor = null): self
    {
        $entry = new self();
        $entry->actor_id = ($actor ?? auth()->user())?->getKey();
        $entry->action = $action;
        $entry->target_type = $target ? class_basename($target) : null;
        $entry->target_id = $target?->getKey();
        $entry->meta = array_diff_key($meta, array_flip(self::SECRET_KEYS)) ?: null;
        $entry->ip = request()->ip();
        $entry->created_at = now();
        $entry->save();

        return $entry;
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** the user an entry is about (all targets are users for now) */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
