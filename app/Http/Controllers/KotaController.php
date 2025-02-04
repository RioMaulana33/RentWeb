<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kota;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        // simpan data
        $base = Kota::create([
           'nama'  => $request->input('nama'),
           'alamat'  => $request->input('alamat'),
           'deskripsi'  => $request->input('deskripsi'),
        ]);

        //response
        return response()->json([
            'status' => true,
            'message' => 'Data Kota telah disimpan'
        ]);
    }
    public function edit($uuid)
    {
        $base = Kota::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function get()
    {
        return response()->json(['data' => Kota::all()]);
    }



    public function update($uuid, Request $request)
    {
        $Kota = Kota::findByUuid($uuid);
        $Kota->update($request->all());
    
        $request->validate([
            'nama' => 'required|string',
            'alamat' => 'required|string',
        ]);
    
        // Update data umum
        $Kota->update($request->only([
            'nama',
            'alamat',
            'deskripsi',
        ]));

         // Jika ada file foto yang diunggah, lakukan update
         if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($Kota->foto) {
                Storage::delete(str_replace('public/', '', $Kota->foto));
            }
    
            // Simpan file foto baru
            $Kota->update([
                'foto' => str_replace('public/', '', $request->file('foto')->store('public/kota')),
            ]);
        }
    
    
        return response()->json([
            'status' => 'true',
            'message' => 'Data berhasil diubah'
        ]);
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
