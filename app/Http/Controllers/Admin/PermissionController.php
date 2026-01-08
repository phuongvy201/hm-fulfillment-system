<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Filter by group
        if ($request->has('group') && $request->group) {
            $query->where('group', $request->group);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $permissions = $query->withCount('roles')
            ->orderBy('group')
            ->orderBy('name')
            ->paginate(20);

        // Get all groups for filter
        $groups = Permission::distinct()->pluck('group')->filter()->sort()->values();

        // Get all roles for assignment
        $roles = Role::orderBy('name')->get();

        return view('admin.permissions.index', compact('permissions', 'groups', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Permission::distinct()->pluck('group')->filter()->sort()->values();
        return view('admin.permissions.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug'],
            'group' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $groups = Permission::distinct()->pluck('group')->filter()->sort()->values();
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.permissions.edit', compact('permission', 'groups', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug,' . $permission->id],
            'group' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission đã được xóa thành công.');
    }

    /**
     * Assign permissions to a role.
     */
    public function assignToRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permissions']);

        return redirect()->back()
            ->with('success', 'Permissions đã được gán cho role thành công.');
    }

    /**
     * Show role permissions management page.
     */
    public function rolePermissions(Role $role)
    {
        $role->load('permissions');
        $allPermissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.permissions.role-permissions', compact('role', 'allPermissions'));
    }
}
