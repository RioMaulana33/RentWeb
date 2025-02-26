<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penyewaan;
use App\Models\StokMobil;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Kota;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Excel\PenyewaanReportExcel;


class PenyewaanController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ? $request->page - 1 : 0;
        $adminUser = Auth::user();

        DB::statement('set @no=0+' . $page * $per);

        $query = Penyewaan::with(['mobil', 'delivery', 'user', 'kota'])
            ->when($request->status != '-', function ($q) use ($request) {
                $q->where('status', $request->status);
            });

        if ($request->start_date && $request->end_date) {
            $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->tahun) {
            $query->whereYear('created_at', $request->tahun);
        }

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

    public function downloadExcel(Request $request)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'LAPORAN PENYEWAAN MOBIL');
            $sheet->mergeCells('A1:J1');
            
            // Set periode
            $dateRangeText = "Periode: " . ($request->start_date ? Carbon::parse($request->start_date)->format('d/m/Y') : 'All Time');
            if ($request->end_date) {
                $dateRangeText .= " - " . Carbon::parse($request->end_date)->format('d/m/Y');
            }
            $sheet->setCellValue('A2', $dateRangeText);
            $sheet->mergeCells('A2:J2');

            // Set headers
            $headers = ['No', 'Kode Penyewaan', 'Customer', 'Mobil', 'Tanggal Mulai', 
                       'Tanggal Selesai', 'Jam Mulai', 'Status', 'Total Biaya', 'Denda'];
            foreach ($headers as $key => $header) {
                $sheet->setCellValue(chr(65 + $key) . '4', $header);
            }

            // Style headers
            $sheet->getStyle('A4:J4')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE1B48F');
            $sheet->getStyle('A4:J4')->getFont()->setBold(true);
            $sheet->getStyle('A4:J4')->getAlignment()->setHorizontal('center');
            
            // Get data
            $query = Penyewaan::with(['mobil', 'user']);
            
                if ($request->start_date) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->end_date) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
                if ($request->status) {
                    $query->where('status', '<=', $request->status);
                }

            $adminUser = Auth::user();
            if ($adminUser && $adminUser->hasRole('admin-kota')) {
                $kotaId = Kota::where('nama', $adminUser->name)->first()->id;
                $query->where('kota_id', $kotaId);
            }
            
            $penyewaans = $query->latest()->get();

            // Fill data
            $row = 5;
            foreach ($penyewaans as $index => $penyewaan) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $penyewaan->kode_penyewaan);
                $sheet->setCellValue('C' . $row, $penyewaan->user->email ?? 'N/A');
                $sheet->setCellValue('D' . $row, $penyewaan->mobil->merk ?? 'N/A');
                $sheet->setCellValue('E' . $row, $penyewaan->tanggal_mulai);
                $sheet->setCellValue('F' . $row, $penyewaan->tanggal_selesai);
                $sheet->setCellValue('G' . $row, $penyewaan->jam_mulai);
                $sheet->setCellValue('H' . $row, $penyewaan->status);
                $sheet->setCellValue('I' . $row, $penyewaan->total_biaya);
                $sheet->setCellValue('J' . $row, $penyewaan->denda ?? 0);
                $row++;
            }

            // Set column width
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setWidth(20);
            }

            // Set borders
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $sheet->getStyle('A4:J'.($row-1))->applyFromArray($styleArray);

            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            
            $filename = 'Laporan_Penyewaan_'.date('Y-m-d').'.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');
            
            $writer->save('php://output');
            exit();
            
        } catch (\Exception $e) {
            // Log error jika diperlukan
            \Log::error('Excel generation error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate Excel file'], 500);
        }
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

    private function getMaintenanceEndTime($tanggal_selesai, $jam_mulai)
    {
        return Carbon::parse("$tanggal_selesai $jam_mulai")->addHours(2);
    }

    private function checkAvailability($mobil_id, $kota_id, $tanggal_mulai, $tanggal_selesai, $jam_mulai)
    {
        // Get car stock data for the specified city
        $stokMobil = StokMobil::where('mobil_id', $mobil_id)
            ->where('kota_id', $kota_id)
            ->first();

        if (!$stokMobil) {
            return [
                'available' => false,
                'message' => 'Mobil tidak tersedia di kota ini'
            ];
        }

        // Convert request dates to Carbon instances for comparison
        $requestStart = Carbon::parse("$tanggal_mulai $jam_mulai");
        $requestEnd = Carbon::parse("$tanggal_selesai $jam_mulai");

        // Get active rentals that might conflict
        $conflictingRentals = Penyewaan::where('mobil_id', $mobil_id)
            ->where('kota_id', $kota_id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'aktif');
            })
            ->get();

        $activeRentals = 0;
        foreach ($conflictingRentals as $rental) {
            $rentalStart = Carbon::parse($rental->tanggal_mulai . ' ' . $rental->jam_mulai);
            $rentalEnd = Carbon::parse($rental->tanggal_selesai . ' ' . $rental->jam_mulai);
            $maintenanceEnd = $this->getMaintenanceEndTime($rental->tanggal_selesai, $rental->jam_mulai);

            // Check various overlap scenarios
            $hasConflict = false;

            // Case 1: Requested period starts during another rental or its maintenance
            if ($requestStart->between($rentalStart, $maintenanceEnd)) {
                $hasConflict = true;
            }

            // Case 2: Requested period ends during another rental or its maintenance
            if ($requestEnd->between($rentalStart, $maintenanceEnd)) {
                $hasConflict = true;
            }

            // Case 3: Requested period completely encompasses another rental
            if ($requestStart->lte($rentalStart) && $requestEnd->gte($maintenanceEnd)) {
                $hasConflict = true;
            }

            if ($hasConflict) {
                $activeRentals++;
            }
        }

        $available = $activeRentals < $stokMobil->stok;

        return [
            'available' => $available,
            'message' => $available
                ? 'Mobil tersedia'
                : 'Mobil tidak tersedia untuk periode ini karena sedang dalam masa sewa atau maintenance',
            'active_rentals' => $activeRentals,
            'total_stock' => $stokMobil->stok,
            'maintenance_end' => $this->getMaintenanceEndTime($tanggal_selesai, $jam_mulai)->format('Y-m-d H:i:s')
        ];
    }



    private function generateKodePenyewaan()
    {
        do {
            $code = strtoupper(Str::random(3) . rand(100, 999));

            $exist = Penyewaan::where('kode_penyewaan', $code)->exists();
        } while ($exist);

        return $code;
    }
    public function checkStok(Request $request)
    {
        $validated = $request->validate([
            'mobil_id' => 'required|integer',
            'kota_id' => 'required|integer',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required|string',
        ]);

        $availability = $this->checkAvailability(
            $validated['mobil_id'],
            $validated['kota_id'],
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $validated['jam_mulai']
        );

        return response()->json([
            'available' => $availability['available'],
            'message' => $availability['message']
        ]);
    }
    public function add(Request $request)
    {
        $validated = $request->validate([
            'mobil_id' => 'required|integer',
            'kota_id' => 'required|integer',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required|string',
            'delivery_id' => 'required|integer',
            'rentaloptions_id' => 'required|integer',
            'total_biaya' => 'required|numeric',
            'alamat_pengantaran' => 'nullable|string',
            'deskripsi_alamat' => 'nullable|string'
        ]);

        // Check car availability including maintenance periods
        $availability = $this->checkAvailability(
            $validated['mobil_id'],
            $validated['kota_id'],
            $validated['tanggal_mulai'],
            $validated['tanggal_selesai'],
            $validated['jam_mulai']
        );

        if (!$availability['available']) {
            return response()->json([
                'status' => false,
                'message' => $availability['message']
            ], 422);
        }

        // Set maintenance end time
        $validated['maintenance_end'] = $availability['maintenance_end'];

        // Create the rental record
        $penyewaan = Penyewaan::create([
            ...$validated,
            'status' => 'pending',
            'kode_penyewaan' => $this->generateKodePenyewaan(),
            'user_id' => auth()->id()
        ]);

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
            ->with(['user', 'mobil', 'kota', 'delivery', 'rentaloption'])
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
            'rentaloptions_id' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'status' => 'required|in:aktif,pending,selesai',
        ]);

        // Update data umum
        $Penyewaaan->update($request->only([
            'tanggal_mulai',
            'tanggal_selesai',
            'rentaloptions_id',
            'status',
            'total_biaya',
            'alamat_pengantaran',
            'deskripsi_alamat',
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
        return DB::transaction(function () use ($uuid) {
            $base = Penyewaan::with(['mobil', 'kota'])
                ->where('uuid', $uuid)
                ->first();

            if (!$base) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data penyewaan tidak ditemukan'
                ], 404);
            }

            // Use the timestamp provided from frontend
            $waktuPengembalianAktual = request('waktu_pengembalian_aktual');

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
                Log::info('Rental completed and stock restored', [
                    'rental_id' => $base->id,
                    'mobil_id' => $base->mobil_id,
                    'kota_id' => $base->kota_id,
                    'current_stock' => $stokMobil->stok,
                    'waktu_pengembalian' => $waktuPengembalianAktual
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

    public function checkRental($orderId)
    {
        $rental = Penyewaan::where('midtrans_booking_code', $orderId)->first();

        return response()->json([
            'exists' => !is_null($rental),
            'rental' => $rental
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
