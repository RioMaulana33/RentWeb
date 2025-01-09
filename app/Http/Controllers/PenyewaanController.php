<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\StokMobil;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Kota;

class PenyewaanController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        $adminUser = Auth::user();
    
        DB::statement('set @no=0+' . $page * $per);
    
        // Base query with relationships
        $query = Penyewaan::with(['mobil', 'delivery', 'user', 'kota']);
    
        if ($adminUser->hasRole('admin-kota')) {
            $kotaId = Kota::where('nama', $adminUser->name)->first()->id;
            $query->where('kota_id', $kotaId);
        }
    
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $search = $request->search;
                $query->where('user_id', 'like', "%$search%")
                    ->orWhere('tanggal_mulai', 'like', "%$search%")
                    ->orWhereHas('mobil', function ($query) use ($search) {
                        $query->where('nama', 'like', "%$search%");
                    })
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    });
            });
        }
    
        $data = $query->latest()
            ->paginate($per, ['*', DB::raw('@no := @no + 1 AS no')]);
    
        return response()->json($data);
    }

    private function checkAvailability($mobil_id, $kota_id, $tanggal_mulai, $tanggal_selesai)
    {
        // Ambil data stok total untuk mobil dan kota tersebut
        $stokMobil = StokMobil::where('mobil_id', $mobil_id)
            ->where('kota_id', $kota_id)
            ->first();

        if (!$stokMobil) {
            return [
                'available' => false,
                'message' => 'Mobil tidak tersedia di kota ini'
            ];
        }

        // Hitung jumlah mobil yang sedang disewa pada rentang tanggal yang diminta
        $activeRentals = Penyewaan::where('mobil_id', $mobil_id)
            ->where(function ($query) use ($tanggal_mulai, $tanggal_selesai) {
                $query->whereBetween('tanggal_mulai', [$tanggal_mulai, $tanggal_selesai])
                    ->orWhereBetween('tanggal_selesai', [$tanggal_mulai, $tanggal_selesai])
                    ->orWhere(function ($q) use ($tanggal_mulai, $tanggal_selesai) {
                        $q->where('tanggal_mulai', '<=', $tanggal_mulai)
                            ->where('tanggal_selesai', '>=', $tanggal_selesai);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->count();

        $available = $activeRentals < $stokMobil->stok;

        return [
            'available' => $available,
            'message' => $available ? 'Mobil tersedia' : 'Mobil tidak tersedia untuk periode ini',
            'active_rentals' => $activeRentals,
            'total_stock' => $stokMobil->stok
        ];
    }

    public function add(Request $request)
    {
        // Validasi data yang diterima
        $validated = $request->validate([
            'mobil_id' => 'required|integer',
            'delivery_id' => 'required|integer',
            'kota_id' => 'required|integer',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required|string',
            'rental_option' => 'required|string',
            'status' => 'required|string',
            'total_biaya' => 'required|numeric',
            'alamat_pengantaran' => 'nullable|string',
        ]);

        // Cek ketersediaan mobil
        $availability = $this->checkAvailability(
            $validated['mobil_id'],
            $validated['kota_id'],
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai']
        );

        if (!$availability['available']) {
            return response()->json([
                'status' => false,
                'message' => $availability['message']
            ], 422);
        }

        // Tambahkan user_id ke data yang akan disimpan
        $validated['user_id'] = auth()->id();
        
        $penyewaan = Penyewaan::create($validated);
    
        return response()->json([
            'status' => true,
            'message' => 'Data penyewaan berhasil disimpan.',
            'data' => $penyewaan,
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
          'delivery_id' => 'required',  
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
            'delivery_id'

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
