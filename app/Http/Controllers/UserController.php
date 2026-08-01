<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\NotificationService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        $totalUsers = $users->count();
        return view('Backend.User.index', compact('users', 'roles', 'totalUsers'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        $this->notificationService->notifyUserCreated(auth()->id(), $user->name);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return view('Backend.User.show', compact('user'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('Backend.User.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        $user->syncRoles($request->role);

        $this->notificationService->notifyUserUpdated(auth()->id(), $user->name);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if ($user->hasRole('Admin') && User::role('Admin')->count() <= 1) {
            abort(403, 'Cannot delete the last admin user.');
        }
        $name = $user->name;
        $user->delete();

        $this->notificationService->notifyUserDeleted(auth()->id(), $name);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
