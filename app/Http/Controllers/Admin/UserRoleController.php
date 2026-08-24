<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Role;
use App\Services\RolePermissionService;
use App\Http\Requests\AssignUserRolesRequest;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function __construct(
        protected RolePermissionService $rolePermissionService
    ) {}

    /**
     * Display user role assignment management interface.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedUserId = $request->input('user_id');

        $users = User::with('roles')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(15)
            ->withQueryString();

        $selectedUser = $selectedUserId ? User::with('roles.permissions')->find($selectedUserId) : $users->first();
        $roles = Role::active()->orderBy('name', 'asc')->get();

        $effectivePermissions = $selectedUser ? $selectedUser->allPermissions()->groupBy('module') : collect();

        return view('admin.users.roles', compact('users', 'selectedUser', 'roles', 'effectivePermissions', 'search'));
    }

    /**
     * Update roles assigned to a user.
     */
    public function updateUserRoles(AssignUserRolesRequest $request)
    {
        $targetUser = User::findOrFail($request->input('user_id'));
        $roleIds = $request->input('roles', []);

        $this->rolePermissionService->assignUserRoles($targetUser, $roleIds);

        return redirect()->route('users.roles', ['user_id' => $targetUser->id])
            ->with('success', "Roles updated successfully for user [{$targetUser->name}]!");
    }
}
