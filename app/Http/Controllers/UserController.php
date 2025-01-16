<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => User::when($request->role_id, function (Builder $query, string $role_id) {
                $query->role($role_id);
            })->get()
        ]);
    }

    /**
     * Display a paginated list of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
    
        $query = User::with('roles')
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('verify_ktp', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
    
        // Filter based on role
        if ($request->role_filter === 'admin') {
            $query->whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'admin-kota']);
            });
        } else if ($request->role_filter === 'user') {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'user');
            });
        }
    
        $query->selectRaw('*, ROW_NUMBER() OVER(ORDER BY created_at DESC) as no');
        $data = $query->paginate($per);
    
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('photo', 'public');
        }

        $user = User::create($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user['role_id'] = $user?->role?->id;
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validatedData['photo'] = $request->file('photo')->store('user', 'public');
        } else {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
                $validatedData['photo'] = null;
            }
        }

        $user->update($validatedData);

        $role = Role::findById($validatedData['role_id']);
        $user->syncRoles($role);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function updateMobile(Request $request)
    {

        $data = $request->only('name', 'phone', 'photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = '/storage/' . $request->file('photo')->store('user', 'public');
        }

        $user = $request->user();
        $user->update($data);

        return response()->json([
            'message' => 'Berhasil memperbarui data',
            'data' => $request->user()
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diubah.'
        ]);
    }

    public function verifyDocument(Request $request)
{
    $user = $request->user();
    $data = [];

    if ($request->hasFile('verify_ktp')) {
        $data['verify_ktp'] = '/storage/' . $request->file('verify_ktp')->store('documents', 'public');
    }
    
    if ($request->hasFile('verify_sim')) {
        $data['verify_sim'] = '/storage/' . $request->file('verify_sim')->store('documents', 'public');
    }

    $user->update($data);

    return response()->json([
        'message' => 'Dokumen berhasil diunggah',
        'data' => $user
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
