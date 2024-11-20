<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyewaan;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PenyewaanController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Penyewaan::with(['mobil',])->when($request->search, function (Builder $query, string $search) {
            $query->where('nama_lengkap', 'like', "%$search%")
                ->orWhere('nip', 'like', "%$search%")
                ->orWhereHas('mobil_id', function ($query) use ($search) {
                    $query->where('nama_poli', 'like', "%$search%");
                });
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);



        return response()->json($data);
    }
    public function add(Request $request)
    {
        // simpan data
        $base = Penyewaan::create([
           'mobil_id' => $request->input('mobil_id'),
           "tanggal_mulai" => $request->input('tanggal_mulai'),
           "tanggal_selesai" => $request->input('tanggal_selesai'),
           "rental_option" => $request->input('rental_option'),
           "status" => $request->input('status'),
           "total_biaya" => $request->input('total_biaya'),
             "alamat_pengantaran" => $request->input('alamat_pengantaran'),
        ]);

        //response
        return response()->json([
            'status' => true,
            'message' => 'Data Penyewaaan telah disimpan'
        ]);
    }
    public function edit($uuid)
    {
        $base = Penyewaan::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function get()
    {
        return response()->json(['data' => Penyewaan::all()]);
    }



    public function update($uuid, Request $request)
    {
        $Penyewaaan = Penyewaan::findByUuid($uuid);
        $Penyewaaan->update($request->all());
    
        $request->validate([
          'mobil_id' => 'required',
          'tanggal_mulai' => 'required|date',
          'tanggal_selesai' => 'required|date',
          'rental_option' => 'required|string',
          'status' => 'required|string',
        ]);
    
        // Update data umum
        $Penyewaaan->update($request->only([
            'tanggal_mulai',
            'tanggal_selesai',
            'rental_option',
            'status',
            'total_biaya',
            'alamat_pengantaran',
            'mobil_id',
        ]));
    
        return response()->json([
            'status' => 'true',
            'message' => 'Data berhasil diubah'
        ]);
    }
    
    public function destroy($uuid)
    {
        $Penyewaaan = Penyewaan::findByUuid($uuid);
        if ($Penyewaaan) {
            $Penyewaaan->delete();
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
