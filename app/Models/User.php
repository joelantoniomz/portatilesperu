<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Comprobar si el usuario tiene uno o varios roles específicos.
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roleList = is_array($roles) ? $roles : func_get_args();

        return $this->roles->pluck('name')->intersect($roleList)->isNotEmpty();
    }

    /**
     * Comprobar si el usuario es Superadministrador.
     */
    public function isSuperAdmin(): bool
    {
        return $this->roles->contains('name', 'superadmin');
    }

    /**
     * Comprobar si el usuario tiene un permiso específico (directo o por rol).
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Asignar un rol por nombre o por ID.
     */
    public function assignRole(string|Role $role): void
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->firstOrFail() : $role;
        $this->roles()->syncWithoutDetaching([$roleModel->id]);
    }

    /**
     * Quitar un rol.
     */
    public function removeRole(string|Role $role): void
    {
        $roleModel = is_string($role) ? Role::where('name', $role)->first() : $role;
        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }
    }

    /**
     * Obtener el rol principal para mostrar en la interfaz.
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
