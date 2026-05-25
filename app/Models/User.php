<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Cached permission keys for the current request.
     * Populated on first call; avoids 2 DB queries per permission check.
     *
     * @var string[]|null
     */
    private ?array $permissionCache = null;

    public function hasRole(string $roleName): bool
    {
        return (string) $this->role === $roleName;
    }

    public function hasAnyRole(): bool
    {
        return ! empty($this->role);
    }

    public function hasPermissionTo(string $permissionKey): bool
    {
        if (! $this->hasAnyRole()) {
            return false;
        }

        if ($this->hasRole('Admin')) {
            return true;
        }

        // Load all permission keys for this user's role in a single query and
        // cache them on the model instance for the lifetime of the request.
        // Previously every call fired: Role::where(...)->first() + permissions()->exists()
        // = 2 DB queries per check. A page with 3 middleware checks = 6 DB queries.
        if ($this->permissionCache === null) {
            $role = Role::query()->where('name', $this->role)->first();
            $this->permissionCache = $role
                ? $role->permissions()->pluck('key')->all()
                : [];
        }

        return in_array($permissionKey, $this->permissionCache, true);
    }
}
