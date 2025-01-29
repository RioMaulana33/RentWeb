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
use App\Models\User;

class PenyewaanController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        $adminUser = Auth::user();
    
        DB::statement('set @no=0+' . $page * $per);
    
        $query = Penyewaan::with(['mobil', 'delivery', 'user', 'kota'])->when($request->status != '-', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
    
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

    public function userRentalHistory(Request $request)
{
    $per = $request->per ?? 10;
    $page = $request->page ? $request->page - 1 : 0;
    $user = Auth::user();
    
    DB::statement('set @no=0+' . $page * $per);
    
    $query = Penyewaan::with(['mobil', 'delivery', 'kota'])
        ->where('user_id', $user->id);
    
    if ($request->search) {
        $query->where(function ($query) use ($request) {
            $search = '%' . $request->search . '%';
            $query->where('tanggal_mulai', 'like', $search)
                ->orWhere('kode_penyewaan', 'like', $search)
                ->orWhere('jam_mulai', 'like', $search)
                ->orWhere('status', 'like', $search)
                ->orWhereHas('mobil', function ($q) use ($search) {
                    $q->where('merk', 'like', $search);
                });
        });
    }
    
    if ($request->status && $request->status != '-') {
        $query->where('status', $request->status);
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
            // ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'selesai')  // Explicitly exclude completed rentals
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
        $base = Penyewaan::where('uuid', $uuid)
            ->with(['user', 'mobil', 'kota', 'delivery']) 
            ->first();
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

    private function hitungDenda($penyewaan, $waktuPengembalianAktual)
    {
        // Konversi string ke instance Carbon untuk perbandingan
        $waktuPengembalianTerjadwal = Carbon::parse($penyewaan->tanggal_selesai . ' ' . $penyewaan->jam_mulai);
        $waktuPengembalian = Carbon::parse($waktuPengembalianAktual);
        
        // Jika tidak terlambat, kembalikan 0
        if ($waktuPengembalian <= $waktuPengembalianTerjadwal) {
            return 0;
        }
        
        // Hitung selisih jam
        $jamTerlambat = $waktuPengembalian->diffInHours($waktuPengembalianTerjadwal);
        
        // Ambil harga sewa per hari
        $hargaPerHari = $penyewaan->total_biaya;
        
        // Hitung denda berdasarkan keterlambatan
        if ($jamTerlambat <= 2) {
            return 0; // Masa tenggang 2 jam
        } elseif ($jamTerlambat <= 6) {
            return $hargaPerHari * 0.5; // 50% dari harga harian
        } elseif ($jamTerlambat <= 24) {
            return $hargaPerHari; // 100% dari harga harian
        } else {
            // Untuk lebih dari 24 jam, hitung per hari penuh
            $hariTambahan = ceil($jamTerlambat / 24);
            return $hargaPerHari * $hariTambahan;
        }
    }

    public function catatPengembalian(Request $request, $uuid)
    {
        $request->validate([
            'waktu_pengembalian_aktual' => 'required|date_format:Y-m-d H:i:s'
        ], [
            'waktu_pengembalian_aktual.required' => 'Waktu pengembalian harus diisi',
            'waktu_pengembalian_aktual.date_format' => 'Format waktu pengembalian tidak valid'
        ]);

        $penyewaan = Penyewaan::findByUuid($uuid);
        
        if (!$penyewaan) {
            return response()->json([
                'status' => false,
                'message' => 'Data penyewaan tidak ditemukan'
            ], 404);
        }

        // Hitung denda
        $denda = $this->hitungDenda($penyewaan, $request->waktu_pengembalian_aktual);

        // Update penyewaan dengan waktu pengembalian aktual dan denda
        $penyewaan->update([
            'waktu_pengembalian_aktual' => $request->waktu_pengembalian_aktual,
            'denda' => $denda,
            'status' => 'selesai'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pengembalian berhasil dicatat',
            'data' => [
                'denda' => $denda,
                'penyewaan' => $penyewaan
            ]
        ]);
    }


    public function clickSelesai($uuid)
    {
        // Wrap everything in a database transaction to ensure data consistency
        return DB::transaction(function () use ($uuid) {
            // Changed from findByUuid
            $base = Penyewaan::with(['mobil', 'kota'])
                ->where('uuid', $uuid)
                ->first();

            if (!$base) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data penyewaan tidak ditemukan'
                ], 404);
            }

            // Set waktu pengembalian aktual ke waktu sekarang
            $waktuPengembalianAktual = now();
            
            // Hitung denda
            $denda = $this->hitungDenda($base, $waktuPengembalianAktual);

            // Update status penyewaan
            $base->update([
                'status' => 'selesai',
                'waktu_pengembalian_aktual' => $waktuPengembalianAktual,
                'denda' => $denda
            ]);

            // Get the related StokMobil record
            $stokMobil = StokMobil::where('mobil_id', $base->mobil_id)
                ->where('kota_id', $base->kota_id)
                ->first();

            if ($stokMobil) {
   
                // Log the stock update for tracking purposes
                Log::info('Rental completed and stock restored', [
                    'rental_id' => $base->id,
                    'mobil_id' => $base->mobil_id,
                    'kota_id' => $base->kota_id,
                    'current_stock' => $stokMobil->stok
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Penyewaan berhasil diselesaikan dan stok dipulihkan',
                'data' => [
                    'denda' => $denda,
                    'penyewaan' => $base
                ]
            ]);
        });
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