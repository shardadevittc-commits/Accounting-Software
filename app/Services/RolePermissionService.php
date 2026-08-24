<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RolePermissionService
{
    /**
     * Create a new custom role with assigned permissions.
     */
    public function createRole(array $data, array $permissionIds = [], ?User $creator = null): Role
    {
        return DB::transaction(function () use ($data, $permissionIds, $creator) {
            $user = $creator ?? Auth::user();

            $slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

            $role = Role::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'is_system' => false,
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]);

            if (!empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
            }

            return $role;
        });
    }

    /**
     * Update an existing role and sync permissions.
     */
    public function updateRole(Role $role, array $data, array $permissionIds = [], ?User $updater = null): Role
    {
        return DB::transaction(function () use ($role, $data, $permissionIds, $updater) {
            $user = $updater ?? Auth::user();

            // Prevent slug modification for system roles
            if (!$role->is_system && !empty($data['name'])) {
                $role->name = $data['name'];
                $role->slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
            }

            if (isset($data['description'])) {
                $role->description = $data['description'];
            }

            if (isset($data['status']) && !$role->is_system) {
                $role->status = $data['status'];
            }

            $role->updated_by = $user?->id;
            $role->save();

            // Sync permissions
            $role->permissions()->sync($permissionIds);

            return $role;
        });
    }

    /**
     * Delete a custom role (prevent deletion of system roles).
     */
    public function deleteRole(Role $role, ?User $performer = null): bool
    {
        if ($role->isSystemRole()) {
            throw new \InvalidArgumentException("System role [{$role->name}] cannot be deleted.");
        }

        return DB::transaction(function () use ($role) {
            return $role->delete();
        });
    }

    /**
     * Assign roles to a target user.
     */
    public function assignUserRoles(User $targetUser, array $roleIds, ?User $assigner = null): void
    {
        DB::transaction(function () use ($targetUser, $roleIds) {
            $targetUser->roles()->sync($roleIds);
            $targetUser->load('roles');
        });
    }

    /**
     * Create a new user with assigned role(s) and custom direct permissions.
     */
    public function createUserWithRoleAndPermissions(array $userData, array $roleIds = [], array $permissionIds = [], ?User $creator = null): User
    {
        return DB::transaction(function () use ($userData, $roleIds, $permissionIds) {
            if (!empty($userData['password'])) {
                $userData['password'] = \Illuminate\Support\Facades\Hash::make($userData['password']);
            }

            $newUser = User::create([
                'name' => $userData['name'],
                'username' => $userData['username'] ?? null,
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);

            if (!empty($roleIds)) {
                $newUser->roles()->sync($roleIds);
            }

            // Direct custom overrides (additional permissions or overrides)
            $newUser->directPermissions()->sync($permissionIds);

            $newUser->load(['roles', 'directPermissions']);

            return $newUser;
        });
    }

    /**
     * Update an existing user's details, role(s), and custom permissions.
     */
    public function updateUserWithRoleAndPermissions(User $targetUser, array $userData, array $roleIds = [], array $permissionIds = [], ?User $updater = null): User
    {
        return DB::transaction(function () use ($targetUser, $userData, $roleIds, $permissionIds) {
            $targetUser->name = $userData['name'];
            if (isset($userData['username'])) {
                $targetUser->username = $userData['username'];
            }
            $targetUser->email = $userData['email'];

            if (!empty($userData['password'])) {
                $targetUser->password = \Illuminate\Support\Facades\Hash::make($userData['password']);
            }

            $targetUser->save();

            // Sync roles
            $targetUser->roles()->sync($roleIds);

            // Sync custom direct permissions
            $targetUser->directPermissions()->sync($permissionIds);

            $targetUser->load(['roles', 'directPermissions']);

            return $targetUser;
        });
    }
}
