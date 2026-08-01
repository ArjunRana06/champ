<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $permissions = Permission::all();
        return view('Backend.Permission.index', compact('permissions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        $this->notificationService->create(auth()->id(), 'success', 'Permission created', "Permission \"{$permission->name}\" has been created.", route('permissions.index'));

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('Backend.Permission.edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        $oldName = $permission->name;
        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        $this->notificationService->create(auth()->id(), 'info', 'Permission updated', "Permission \"{$oldName}\" has been updated.", route('permissions.index'));

        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $name = $permission->name;
        $permission->delete();

        $this->notificationService->create(auth()->id(), 'warning', 'Permission deleted', "Permission \"{$name}\" has been deleted.", route('permissions.index'));

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
