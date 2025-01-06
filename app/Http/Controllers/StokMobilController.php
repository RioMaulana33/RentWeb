<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StokMobil;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class StokMobilController extends Controller
{
    // Menampilkan data stok mobil
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);

        $data = StokMobil::with(['mobil', 'kota'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->whereHas('mobil', function ($q) use ($search) {
                    $q->where('merk', 'like', "%$search%");
                })
                ->orWhereHas('kota', function ($q) use ($search) {
                    $q->where('nama', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    // Menambah data stok baru
    public function add(Request $request)
    {
        $validated = $request->validate([
            'mobil_id' => 'required|integer|exists:mobil,id',
            'kota_id' => 'required|integer|exists:kota,id',
            'stok' => 'required|integer|min:0',
        ]);
    
        // Cek apakah kombinasi sudah ada
        $existing = StokMobil::where('mobil_id', $validated['mobil_id'])
            ->where('kota_id', $validated['kota_id'])
            ->first();
    
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Data mobil dan kota sudah ada!',
            ], 422);
        }
    
        $stokMobil = StokMobil::create($validated);
    
        return response()->json([
            'status' => true,
            'message' => 'Stok mobil berhasil ditambahkan.',
            'data' => $stokMobil,
        ]);
    }
    

    // Mengambil data stok untuk diedit
    public function edit($uuid)
    {
        $stokMobil = StokMobil::findByUuid($uuid);

        return response()->json([
            'data' => $stokMobil,
        ]);
    }

    public function get()
    {
        return response()->json(['data' => StokMobil::all()]);
    }

    // Mengupdate data stok
    public function update($uuid, Request $request)
    {
        $stokMobil = StokMobil::findByUuid($uuid);
        $stokMobil->update($request->all());

        $request->validate([
            'mobil_id' => 'required|integer|exists:mobil,id',
            'kota_id' => 'required|integer|exists:kota,id',
            'stok' => 'required|integer|',
        ]);

        $stokMobil->update($request->only([
            'mobil_id',
            'kota_id',
            'stok',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Stok mobil berhasil diupdate.',
            'data' => $stokMobil
        ]);
    }

    // Menghapus data stok
    public function destroy($uuid)
    {
        $stokMobil = StokMobil::findByUuid($uuid);
        if ($stokMobil) {
            $stokMobil->delete();
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
