<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\StokMobil;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Helpers\RentalHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
    
        $query = Penyewaan::with(['mobil', 'delivery', 'user', 'kota']);
    
        if ($adminUser->hasRole('admin-kota')) {
            $kotaId = Kota::where('nama', $adminUser->name)->first()->id;
            $query->where('kota_id', $kotaId);
        }
    
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $search = '%' . $request->search . '%';
                $query->where('user_id', 'like', $search)
                    ->orWhere('tanggal_mulai', 'like', $search)
                    ->orWhere('kode_penyewaan', 'like', $search)
                    ->orWhere('jam_mulai', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhereHas('mobil', function ($q) use ($search) {
                        $q->where('merk', 'like', $search); // Sesuaikan dengan nama kolom yang benar
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('email', 'like', $search);
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

    private function generateKodePenyewaan()
    {
        do{
            $code = strtoupper(Str::random(3) . rand(100, 999));

            $exist = Penyewaan::where('kode_penyewaan', $code)->exists();
        } while ($exist);

        return $code;
    }

   
    public function add(Request $request)
    {
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

        // Set initial status to pending
        $validated['status'] = 'pending';

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

        $validated['kode_penyewaan'] = $this->generateKodePenyewaan();
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

    public function detail($uuid)
    {
        $base = Penyewaan::where('uuid', $uuid)->with(['user', 'Mobil', 'Kota'])->first();
        return response()->json([
            'success' => true,
            'data' => $base
        ]); 
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
          'status' => 'required|in:aktif,pending,selesai',
        ]);
    
        // Update data umum
        $Penyewaaan->update($request->only([
            'tanggal_mulai',
            'tanggal_selesai',
            'rental_option',
            'status' ,
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

    public function clickAktif($uuid)
    {
        $base = Penyewaan::findByUuid($uuid);

        if (!$base) {
            return response()->json([
                'status' => 'false',
                'message' => 'Penyewaan tidak ditemukan'
            ], 404); // 404 Not Found
        }

        $base->update([
            'status' => 'aktif',

        ]);

        return response()->json([
            'status' => 'true',
            'message' => 'Penyewaan berhasil di ubah'
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
