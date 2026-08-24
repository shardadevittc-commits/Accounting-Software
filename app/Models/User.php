<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

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
        'username',
        'email',
        'password',
        'avatar',
        'theme_color',
    ];

    /**
     * The attributes that should be hidden for serialization.
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
     * Accessor for full Avatar Public URL using Laravel Storage disk.
     */
    public function getAvatarUrlAttribute(): string
    {
        $disk = config('profile.disk', 'public');

        if ($this->avatar && Storage::disk($disk)->exists($this->avatar)) {
            return '/storage/' . ltrim($this->avatar, '/');
        }

        $defaultPath = config('profile.default_avatar', 'assets/images/avatar-3d.png');
        return '/' . ltrim($defaultPath, '/');
    }

    /**
     * Roles belonging to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Direct permission overrides assigned to the user.
     */
    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('Admin') || $this->email === 'admin@gmail.com';
    }

    public function isSuperAdmin(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user has a specific role (by slug or name or array of roles).
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->relationLoaded('roles')) {
            $userRoleSlugs = $this->roles->pluck('slug')->toArray();
            $userRoleNames = $this->roles->pluck('name')->toArray();
        } else {
            $userRoleSlugs = $this->roles()->pluck('slug')->toArray();
            $userRoleNames = $this->roles()->pluck('name')->toArray();
        }

        $userRoles = array_merge($userRoleSlugs, $userRoleNames);

        if (is_array($roles)) {
            return count(array_intersect($roles, $userRoles)) > 0;
        }

        return in_array($roles, $userRoles);
    }

    /**
     * Check if user has a specific permission slug.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->allPermissions()->contains('slug', $permissionSlug);
    }

    /**
     * Get collection of all effective permissions for the user (Role Permissions + Direct User Permissions).
     */
    public function allPermissions()
    {
        $rolePermissions = $this->roles()
            ->where('roles.status', 'active')
            ->with(['permissions' => function ($q) {
                $q->where('permissions.status', 'active');
            }])
            ->get()
            ->pluck('permissions')
            ->flatten();

        $directPermissions = $this->directPermissions()
            ->where('permissions.status', 'active')
            ->get();

        return $rolePermissions->concat($directPermissions)->unique('id');
    }
}
