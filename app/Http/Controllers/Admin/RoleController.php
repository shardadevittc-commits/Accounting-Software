<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\Permission;
use App\Services\RolePermissionService;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RolePermissionService $rolePermissionService
    ) {}

    /**
     * Display a listing of all roles with summary stats and filters.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $module = $request->input('module');

        $query = Role::withCount(['users', 'permissions'])
            ->with(['creator', 'updater'])
            ->search($search);

        if ($status && in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }

        if ($module) {
            $query->whereHas('permissions', function ($q) use ($module) {
                $q->where('module', $module);
            });
        }

        $roles = $query->orderBy('is_system', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(12)
            ->withQueryString();

        $modules = Permission::distinct()->pluck('module');

        $stats = [
            'total_roles' => Role::count(),
            'active_roles' => Role::where('status', 'active')->count(),
            'system_roles' => Role::where('is_system', true)->count(),
            'custom_roles' => Role::where('is_system', false)->count(),
        ];

        return view('admin.roles.index', compact('roles', 'modules', 'stats', 'search', 'status', 'module'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $groupedPermissions = Permission::getGroupedByModule();
        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in database.
     */
    public function store(StoreRoleRequest $request)
    {
        $role = $this->rolePermissionService->createRole(
            $request->validated(),
            $request->input('permissions', [])
        );

        return redirect()->route('roles.show', $role->id)
            ->with('success', "Role [{$role->name}] created successfully!");
    }

    /**
     * Display the specified role details and assigned users.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users', 'creator', 'updater']);
        
        $groupedPermissions = $role->permissions->groupBy('module');

        return view('admin.roles.show', compact('role', 'groupedPermissions'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $groupedPermissions = Permission::getGroupedByModule();
        $assignedPermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'assignedPermissionIds'));
    }

    /**
     * Update the specified role in database.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $updatedRole = $this->rolePermissionService->updateRole(
            $role,
            $request->validated(),
            $request->input('permissions', [])
        );

        return redirect()->route('roles.show', $updatedRole->id)
            ->with('success', "Role [{$updatedRole->name}] updated successfully!");
    }

    /**
     * Remove the specified custom role from database.
     */
    public function destroy(Role $role)
    {
        if ($role->isSystemRole()) {
            return redirect()->back()->with('error', "System role [{$role->name}] is protected and cannot be deleted.");
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', "Cannot delete role [{$role->name}] because it is currently assigned to {$role->users()->count()} user(s). Reassign users first.");
        }

        $this->rolePermissionService->deleteRole($role);

        return redirect()->route('roles.index')
            ->with('success', "Role deleted successfully!");
    }
}
