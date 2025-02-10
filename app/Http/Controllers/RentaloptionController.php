<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rentaloption;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RentaloptionController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;

        DB::statement('set @no=0+' . $page * $per);
        $data = Rentaloption::when($request->search, function (Builder $query, string $search) {
            $query->where('nama', 'like', "%$search%");
            $query->orWhere('deskripsi', 'like', "%$search%");
            $query->orWhere('biaya', 'like', "%$search%");
        })->latest()->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function add(Request $request)
    {
        // simpan data
        $base = Rentaloption::create([
            'nama'  => $request->input('nama'),
            'deskripsi'  => $request->input('deskripsi'),
            'biaya'  => $request->input('biaya'),

        ]);

        //response
        return response()->json([
            'status' => true,
            'message' => 'Data Rentaloption telah disimpan'
        ]);
    }
    public function edit($uuid)
    {
        $base = Rentaloption::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function get()
    {
        return response()->json(['data' => Rentaloption::all()]);
    }



    public function update($uuid, Request $request)
    {
        $Rentaloption = Rentaloption::findByUuid($uuid);
        $Rentaloption->update($request->all());
    
        // Update data umum
        $Rentaloption->update($request->only([
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
        $Rentaloption = Rentaloption::findByUuid($uuid);
        if ($Rentaloption) {
            $Rentaloption->delete();
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
