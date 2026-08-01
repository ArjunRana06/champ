<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $roles = Role::with('permissions')->latest()->paginate(10);
        $permissions = Permission::all();
        return view('Backend.Role.index', compact('roles', 'permissions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'nullable|string|in:web,api',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $this->notificationService->create(auth()->id(), 'success', 'Role created', "Role \"{$role->name}\" has been created.", route('roles.index'));

        return redirect()->route('roles.index')->with('success', 'Role created with permissions.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('Backend.Role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'guard_name' => 'nullable|string|in:web,api',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $oldName = $role->name;
        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        $this->notificationService->create(auth()->id(), 'info', 'Role updated', "Role \"{$oldName}\" has been updated.", route('roles.index'));

        return redirect()->route('roles.index')->with('success', 'Role updated with permissions.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Admin') {
            abort(403, 'Cannot delete the Admin role.');
        }
        $name = $role->name;
        $role->delete();

        $this->notificationService->create(auth()->id(), 'warning', 'Role deleted', "Role \"{$name}\" has been deleted.", route('roles.index'));

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
