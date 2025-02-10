<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function userWishlist(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        $user = Auth::user();

        DB::statement('set @no=0+' . $page * $per);

        $query = Wishlist::with(['mobil', 'user', 'kota'])
            ->where('user_id', $user->id);


        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $search = '%' . $request->search . '%';
                $query->whereHas('mobil', function ($q) use ($search) {
                    $q->where('merk', 'like', $search)
                        ->orWhere('model', 'like', $search);
                });
            });
        }

        $data = $query->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);

        return response()->json($data);
    }

    public function add(Request $request)
    {
        // simpan data
        $base = Wishlist::create([
            'mobil_id'  => $request->input('mobil_id'),
            'user_id'  => $request->input('user_id'),
            'kota_id'  => $request->input('kota_id'),
        ]);

        //response
        return response()->json([
            'status' => true,
            'message' => 'Data Wishlist telah disimpan'
        ]);
    }
    public function edit($uuid)
    {
        $base = Wishlist::findByUuid($uuid);

        return response()->json([
            'data' => $base,
        ], 200);
    }

    public function get()
    {
        return response()->json(['data' => Wishlist::all()]);
    }



    public function update($uuid, Request $request)
    {
        $Wishlist = Wishlist::findByUuid($uuid);
        $Wishlist->update($request->all());

        $request->validate([
            'mobil_id' => 'required|string',
            'user_id' => 'required|string',
            'kota_id' => 'required|string',
        ]);

        // Update data umum
        $Wishlist->update($request->only([
            'mobil_id',
            'user_id',
            'kota_id',
        ]));

        return response()->json([
            'status' => 'true',
            'message' => 'Data berhasil diubah'
        ]);
    }

    public function destroy($uuid)
    {
        $wishlist = Wishlist::where('uuid', $uuid)->first();

        if (!$wishlist) {
            return response()->json([
                'message' => "Data tidak ditemukan"
            ], 404);
        }

        try {
            $wishlist->delete();
            return response()->json([
                'message' => "Data telah dihapus",
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Gagal menghapus data",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
