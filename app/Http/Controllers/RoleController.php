<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Role::all()
        ]);
    }

    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Role::when($request->search, function (Builder $query, string $search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('full_name', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $validatedData = $request->validated();
        $role = Role::create([
            'name' => $validatedData['name'],
            'guard_name' => 'api',
            'full_name' => $validatedData['full_name']
        ]);
        $role->syncPermissions($validatedData['permissions']);
        return response()->json([
            'success' => true,
            'role' => $role,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return response()->json([
            'role' => [
                'name' => $role->name,
                'full_name' => $role->full_name,
                'permissions' => $role->permissions->pluck('name')
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role)
    {
        $validatedData = $request->validated();

        $role->update([
            'name' => $validatedData['name'],
            'guard_name' => 'api',
            'full_name' => $validatedData['full_name']
        ]);

        $role->syncPermissions($validatedData['permissions']);

        return response()->json([
            'success' => true,
            'role' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->revokePermissionTo($role->permissions);
        $role->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
