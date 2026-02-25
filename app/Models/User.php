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

        $role = Role::query()->where('name', $this->role)->first();
        if (! $role) {
            return false;
        }

        return $role->permissions()->where('key', $permissionKey)->exists();
    }
}
