<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\RolePermissionService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected RolePermissionService $rolePermissionService
    ) {}

    /**
     * Display a listing of all system users.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleId = $request->input('role_id');

        $query = User::with(['roles', 'directPermissions']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId) {
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        $users = $query->orderBy('id', 'asc')
            ->paginate(12)
            ->withQueryString();

        $roles = Role::where('status', 'active')->orderBy('name', 'asc')->get();

        $stats = [
            'total_users' => User::count(),
            'super_admins' => User::where('email', 'admin@gmail.com')->orWhereHas('roles', function ($q) {
                $q->where('slug', 'admin');
            })->count(),
            'active_roles' => Role::where('status', 'active')->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'stats', 'search', 'roleId'));
    }

    /**
     * Show the form for creating a new user with role and permissions selection.
     */
    public function create()
    {
        $roles = Role::where('status', 'active')->with('permissions')->orderBy('name', 'asc')->get();
        $groupedPermissions = Permission::getGroupedByModule();

        return view('admin.users.create', compact('roles', 'groupedPermissions'));
    }

    /**
     * Store a newly created user in database.
     */
    public function store(StoreUserRequest $request)
    {
        $userData = $request->only(['name', 'username', 'email', 'password']);
        $roleId = $request->input('role_id');
        $permissionIds = $request->input('permissions', []);

        $newUser = $this->rolePermissionService->createUserWithRoleAndPermissions(
            $userData,
            [$roleId],
            $permissionIds
        );

        return redirect()->route('users.index')
            ->with('success', "User [{$newUser->name}] created successfully with assigned role & permissions!");
    }

    /**
     * Show the form for editing an existing user.
     */
    public function edit(User $user)
    {
        $user->load(['roles', 'directPermissions']);

        $roles = Role::where('status', 'active')->with('permissions')->orderBy('name', 'asc')->get();
        $groupedPermissions = Permission::getGroupedByModule();

        $currentRoleId = $user->roles->first()?->id;

        // Effective permission IDs assigned to user (Role permissions + Direct user permissions)
        $assignedPermissionIds = $user->allPermissions()->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'groupedPermissions', 'currentRoleId', 'assignedPermissionIds'));
    }

    /**
     * Update the specified user in database.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $userData = array_filter($request->only(['name', 'username', 'email', 'password']));
        $roleId = $request->input('role_id');
        $permissionIds = $request->input('permissions', []);

        $updatedUser = $this->rolePermissionService->updateUserWithRoleAndPermissions(
            $user,
            $userData,
            [$roleId],
            $permissionIds
        );

        return redirect()->route('users.index')
            ->with('success', "User [{$updatedUser->name}] updated successfully!");
    }

    /**
     * API Endpoint: Get permissions assigned to a specific role.
     */
    public function getRolePermissions(Role $role)
    {
        $permissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        return response()->json([
            'success' => true,
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permission_ids' => $permissionIds,
        ]);
    }

    /**
     * Remove the specified user from database.
     */
    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', "Super Admin account [{$user->email}] cannot be deleted.");
        }

        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', "You cannot delete your own currently logged-in account.");
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User [{$userName}] deleted successfully!");
    }
}
