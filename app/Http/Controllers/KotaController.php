<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kota;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KotaController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Kota::when($request->search, function (Builder $query, string $search) {
            $query->where('nama', 'like', "%$search%")
                ->orWhere('alamat', 'like', "%$search%")
                ->orWhere('deskripsi', 'like', "%$search%");
        })->orderBy('nama')->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
    
        // Check if city name already exists
        $existing = Kota::where('nama', $validated['nama'])->first();
    
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Nama kota sudah ada!',
            ], 422);
        }
    
        $base = Kota::create([
            'nama' => $request->input('nama'),
            'alamat' => $request->input('alamat'),
            'deskripsi' => $request->input('deskripsi'),
            'foto' => str_replace('public/', '', $request->file('foto')->store('public/kota')),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Data Kota telah disimpan'
        ]);
    }
    
    public function update($uuid, Request $request)
    {
        $kota = Kota::findByUuid($uuid);
        
        $validated = $request->validate([
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
    
        // Check if city name exists but ignore current record
        $existing = Kota::where('nama', $validated['nama'])
            ->where('id', '!=', $kota->id)
            ->first();
    
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Nama kota sudah ada!',
            ], 422);
        }
    
        $kota->update([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'deskripsi' => $request->deskripsi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    
        if ($request->hasFile('foto')) {
            if ($kota->foto) {
                Storage::delete(str_replace('public/', '', $kota->foto));
            }
            
            $kota->update([
                'foto' => str_replace('public/', '', $request->file('foto')->store('public/kota')),
            ]);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diubah'
        ]);
    }

    public function edit($uuid)
    {
        $base = Kota::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function getByUser()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin-kota')) {
            $kota = Kota::where('nama', $user->name)->get();
        } else {
            $kota = Kota::all();
        }

        return response()->json([
            'status' => true,
            'data' => $kota
        ]);
    }

    public function checkRole()
{
    $user = Auth::user();
    $role = $user->hasRole('admin-kota') ? 'admin-kota' : 'admin';
    
    return response()->json([
        'status' => true,
        'role' => $role
    ]);
}

    public function get()
    {
        return response()->json(['data' => Kota::all()]);
    }
    
    public function destroy($uuid)
    {
        $Kota = Kota::findByUuid($uuid);
        if ($Kota) {
            $Kota->delete();
            return response()->json([
                'message' => "Data telah dihapus",
                'code' => 200
            ]);
        } else {
            return response([
                'message' => "gagal menghapus $uuid / data tidak ditemukan"
            ]);
        }
    }
}
