<?php

namespace App\Excel;

use App\Models\Penyewaan;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Auth;
use App\Models\Kota;

class PenyewaanReportExcel extends ReportExcel
{
    protected $startDate;
    protected $endDate;
    protected $tahun;

    public function __construct($startDate = null, $endDate = null, $tahun = null)
    {
        // Ensure dates are properly formatted
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
        $this->tahun = $tahun;
    }

    public function download($filename)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set default row height and column width to prevent Excel corruption
            $sheet->getDefaultRowDimension()->setRowHeight(15);
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setWidth(12);
            }

            $sheet->setCellValue('A1', 'LAPORAN PENYEWAAN MOBIL');
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->applyFromArray($this->font18BoldCenter);

            $dateRangeText = "Periode: ";
            if ($this->tahun) {
                $dateRangeText .= "Tahun " . $this->tahun;
            } else {
                $dateRangeText .= ($this->startDate ? $this->startDate->format('d/m/Y') : 'All Time');
                if ($this->endDate) {
                    $dateRangeText .= " - " . $this->endDate->format('d/m/Y');
                }
            }
            
            $sheet->setCellValue('A2', $dateRangeText);
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->applyFromArray($this->font14BoldCenter);

            $headers = [
                'No', 'Kode Penyewaan', 'Customer', 'Mobil', 'Tanggal Mulai', 
                'Tanggal Selesai', 'Jam Mulai', 'Status', 'Total Biaya', 'Denda'
            ];

            foreach ($headers as $key => $header) {
                $sheet->setCellValue(chr(65 + $key) . '4', $header);
            }
            
            $sheet->getStyle('A4:J4')->applyFromArray($this->fontBoldCenter);
            $sheet->getStyle('A4:J4')->applyFromArray($this->borderAll);

            $query = Penyewaan::with(['mobil', 'user']);
            
            if ($this->tahun) {
                $query->whereYear('created_at', $this->tahun);
            } else {
                if ($this->startDate) {
                    $query->where('created_at', '>=', $this->startDate);
                }
                
                if ($this->endDate) {
                    $query->where('created_at', '<=', $this->endDate);
                }
            }
            
            $adminUser = Auth::user();
            if ($adminUser && $adminUser->hasRole('admin-kota')) {
                $kotaId = Kota::where('nama', $adminUser->name)->first()->id;
                $query->where('kota_id', $kotaId);
            }
            
            $penyewaans = $query->latest()->get();

            $row = 5;
            $totalBiaya = 0;
            $totalDenda = 0;
            
            foreach ($penyewaans as $index => $penyewaan) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $penyewaan->kode_penyewaan);
                $sheet->setCellValue('C' . $row, $penyewaan->user->email ?? 'N/A');
                $sheet->setCellValue('D' . $row, $penyewaan->mobil->merk ?? 'N/A');
                $sheet->setCellValue('E' . $row, Carbon::parse($penyewaan->tanggal_mulai)->format('Y-m-d'));
                $sheet->setCellValue('F' . $row, Carbon::parse($penyewaan->tanggal_selesai)->format('Y-m-d'));
                $sheet->setCellValue('G' . $row, $penyewaan->jam_mulai);
                $sheet->setCellValue('H' . $row, $penyewaan->status);
                $sheet->setCellValue('I' . $row, (float)$penyewaan->total_biaya);
                $sheet->setCellValue('J' . $row, (float)($penyewaan->denda ?? 0));
                
                $totalBiaya += (float)$penyewaan->total_biaya;
                $totalDenda += (float)($penyewaan->denda ?? 0);
                
                $row++;
            }
            
            $summaryRow = $row + 1;
            $sheet->setCellValue('H' . $summaryRow, 'TOTAL:');
            $sheet->setCellValue('I' . $summaryRow, $totalBiaya);
            $sheet->setCellValue('J' . $summaryRow, $totalDenda);
            $sheet->getStyle('H' . $summaryRow . ':J' . $summaryRow)->applyFromArray($this->fontBold);
            $sheet->getStyle('I' . $summaryRow . ':J' . $summaryRow)->applyFromArray($this->fontAlignRight);

            $sheet->getStyle('A5:J' . ($row - 1))->applyFromArray($this->borderAll);
            $sheet->getStyle('I5:J' . ($row - 1))->applyFromArray($this->fontAlignRight);
            
            // Format numbers properly
            $sheet->getStyle('I5:J' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0');

            // Set column widths after data is populated
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            
            // Use a more reliable temporary file path
            $tempFilePath = storage_path('app/public/temp/' . uniqid() . '.xlsx');
            $tempDir = dirname($tempFilePath);
            
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            $writer->save($tempFilePath);
            
            return response()->download($tempFilePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'. $filename .'"',
                'Cache-Control' => 'max-age=0',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Excel generation error: ' . $e->getMessage());
            throw $e;
        }
    }
}