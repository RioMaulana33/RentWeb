<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Models\StokMobil;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MobilController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Mobil::when($request->search, function (Builder $query, string $search) {
            $query->where('merk', 'like', "%$search%")
                ->orWhere('tahun', 'like', "%$search%")
                ->orWhere('model', 'like', "%$search%")
                ->orWhere('type', 'like', "%$search%")
                ->orWhere('tarif', 'like', "%$search%")
                ->orWhere('kapasitas', 'like', "%$search%")
                ->orWhere('bahan_bakar', 'like', "%$search%");
              
                


        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    // public function getMobilByKota(Request $request, $kota_id)
    // {
    //     $data = StokMobil::with('mobil', 'kota')
    //         ->where('kota_id', $kota_id)
    //         ->where('stok', '>', 0) 
    //         ->get();
    
    //     return response()->json([
    //         'status' => true,
    //         'data' => $data,
    //     ]);
    // }

    public function getById($id)
{
    try {
        $data = Mobil::with(['stokmobil.kota'])->findOrFail($id);
        
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengambil data mobil: ' . $e->getMessage()
        ], 500);
    }
}

public function getMobilByKota(Request $request, $kota_id)
{
    try {
        $query = StokMobil::with(['mobil', 'kota'])
            ->where('kota_id', $kota_id)
            ->where('stok', '>', 0);

        // Apply search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('mobil', function (Builder $q) use ($search) {
                $q->where('merk', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Apply filters
        if ($request->has('priceRange')) {
            $query->whereHas('mobil', function (Builder $q) use ($request) {
                switch ($request->priceRange) {
                    case 'under200':
                        $q->where('tarif', '<', 200000);
                        break;
                    case '200to500':
                        $q->whereBetween('tarif', [200000, 500000]);
                        break;
                    case 'above500':
                        $q->where('tarif', '>', 500000);
                        break;
                }
            });
        }

        if ($request->has('fuelType') && $request->fuelType !== 'all') {
            $query->whereHas('mobil', function (Builder $q) use ($request) {
                $q->where('bahan_bakar', $request->fuelType);
            });
        }
        if ($request->has('model') && $request->model !== 'all') {
            $query->whereHas('mobil', function (Builder $q) use ($request) {
                $q->where('model', $request->model);
            });
        }

        if ($request->has('carType') && $request->carType !== 'all') {
            $query->whereHas('mobil', function (Builder $q) use ($request) {
                $q->where('type', $request->carType);
            });
        }

        if ($request->has('capacity') && $request->capacity !== 'all') {
            $query->whereHas('mobil', function (Builder $q) use ($request) {
                $q->where('kapasitas', $request->capacity);
            });
        }

        $data = $query->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengambil data mobil: ' . $e->getMessage()
        ], 500);
    }
}

    
    public function add(Request $request)
    {
        // simpan data
        $base = Mobil::create([
            'merk'  => $request->input('merk'),
            'model'  => $request->input('model'),
            'type'  => $request->input('type'),
            'tahun'  => $request->input('tahun'),
            'tarif'  => $request->input('tarif'),
            'kapasitas'  => $request->input('kapasitas'),
            'bahan_bakar'  => $request->input('bahan_bakar'),
            'foto' => str_replace('public/', '', $request->file('foto')->store('public/mobil')),

        ]);

        //response
        return response()->json([
            'status' => true,
            'message' => 'Data Mobil telah disimpan'
        ]);
    }
    public function edit($uuid)
    {
        $base = Mobil::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function get()
    {
        return response()->json(['data' => Mobil::all()]);
    }



    public function update($uuid, Request $request)
    {
        $mobil = Mobil::findByUuid($uuid);
        $mobil->update($request->all());
    
        // Validasi untuk data umum tanpa memaksa `foto` harus diisi
        $request->validate([
            'merk' => 'required|string',
            'model' => 'required|string',
            'type' => 'nullable|string',
            'tahun' => 'nullable|integer',
            'tarif' => 'required|numeric',
            'kapasitas' => 'required|integer',
            'bahan_bakar' => 'nullable|string',
        ]);
    
        // Update data umum
        $mobil->update($request->only([
            'merk',
            'model',
            'type',
            'tahun',
            'tarif',
            'kapasitas',
            'bahan_bakar',
        ]));
    
        // Jika ada file foto yang diunggah, lakukan update
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($mobil->foto) {
                Storage::delete(str_replace('public/', '', $mobil->foto));
            }
    
            // Simpan file foto baru
            $mobil->update([
                'foto' => str_replace('public/', '', $request->file('foto')->store('public/mobil')),
            ]);
        }
    
        return response()->json([
            'status' => 'true',
            'message' => 'Data berhasil diubah'
        ]);
    }
    
    public function destroy($uuid)
    {
        $Mobil = Mobil::findByUuid($uuid);
        if ($Mobil) {
            $Mobil->delete();
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
