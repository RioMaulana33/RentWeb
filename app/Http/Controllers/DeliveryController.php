<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Delivery::when($request->search, function (Builder $query, string $search) {
            $query->where('nama', 'like', "%$search%");
            $query->orWhere('deskripsi', 'like', "%$search%");
            $query->orWhere('biaya', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function add(Request $request)
    {
        // simpan data
        $base = Delivery::create([
            'nama'  => $request->input('nama'),
            'deskripsi'  => $request->input('deskripsi'),
            'biaya'  => $request->input('biaya'),

        ]);

        //response
        return response()->json([
            'status' => true,
            'message' => 'Data Delivery telah disimpan'
        ]);
    }
    public function edit($uuid)
    {
        $base = Delivery::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function get()
    {
        return response()->json(['data' => Delivery::all()]);
    }



    public function update($uuid, Request $request)
    {
        $Delivery = Delivery::findByUuid($uuid);
        $Delivery->update($request->all());
    
        // Update data umum
        $Delivery->update($request->only([
            'nama',
            'deskripsi',
            'biaya',
        ]));
    
        return response()->json([
            'status' => 'true',
            'message' => 'Data berhasil diubah'
        ]);
    }
    
    public function destroy($uuid)
    {
        $Delivery = Delivery::findByUuid($uuid);
        if ($Delivery) {
            $Delivery->delete();
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
