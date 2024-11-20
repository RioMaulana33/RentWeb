<?php

namespace App\Excel;

use App\Helpers\AppHelper;
use App\Models\Hutang;
use App\Models\Piutang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KartuPiutangHutangExcel extends ReportExcel
{
    private $spreadsheet;
    private $periode;
    private $data;
    private $saldo_awal;
    private $title;
    private $tipe;

    public function __construct($periode, $data, $saldo_awal, $title, $tipe)
    {
        $this->periode = $periode;
        $this->data = $data;
        $this->saldo_awal = $saldo_awal;
        $this->title = $title;
        $this->tipe = $tipe;
        $this->view();
    }

    public function view()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        $sheet->setCellValue('A1', $this->title);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1:F3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:F1')->getFont()
            ->setBold(true)
            ->setSize(15);
        $row = 3;

        $sheet->setCellValue('A' . $row, 'TANGGAL');
        $sheet->setCellValue('B' . $row, 'SUMBER');
        $sheet->setCellValue('C' . $row, 'JATUH TEMPO');
        $sheet->setCellValue('D' . $row, 'DEBIT');
        $sheet->setCellValue('E' . $row, 'KREDIT');
        $sheet->setCellValue('F' . $row, 'SALDO');

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->getStyle('A' . $row . ':' . 'F' . $row)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $row++;
        $no = 1;

        $sheet->setCellValue('F' . $row, $this->saldo_awal);
        $sheet->getCell('F' . $row)->getCalculatedValue();
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
        $row++;

        foreach ($this->data as $item) {
            $sheet->setCellValue('A' . $row, AppHelper::tanggal_indo($item->tanggal));

            if ($this->tipe == "piutang") {
                if ($item::class == Piutang::class) {
                    $sheet->setCellValue('B' . $row, 'Penjualan ' . $item->penjualan->kode);
                    $sheet->setCellValue('C' . $row, $item->jatuh_tempo);
                    $sheet->setCellValue('D' . $row, $item->total);
                    $this->saldo_awal += $item->total;
                } else {
                    $sheet->setCellValue('E' . $row, $item->bayar);
                    $this->saldo_awal -= $item->bayar;
                }
            } else if ($this->tipe == "hutang") {
                if ($item::class == Hutang::class) {
                    $sheet->setCellValue('B' . $row, 'Pembelian ' . $item->pembelian->kode);
                    $sheet->setCellValue('C' . $row, $item->jatuh_tempo);
                    $sheet->setCellValue('D' . $row, $item->total);
                    $this->saldo_awal += $item->total;
                } else {
                    $sheet->setCellValue('E' . $row, $item->bayar);
                    $this->saldo_awal -= $item->bayar;
                }
            }

            $sheet->setCellValue('F' . $row, $this->saldo_awal);
            $sheet->getCell('F' . $row)->getCalculatedValue();
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');

            $sheet->getCell('C' . $row)->getCalculatedValue();
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('D' . $row)->getCalculatedValue();
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('E' . $row)->getCalculatedValue();
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');

            $row++;
        }

        $this->spreadsheet = $spreadsheet;
    }

    public function download($filename = "")
    {
        $writer = new Xlsx($this->spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        $writer->save("php://output");
    }
}
