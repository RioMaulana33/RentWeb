<?php

namespace App\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NeracaSaldoExcel extends ReportExcel
{
    private $spreadsheet;
    private $periode;
    private $data;
    private $title;

    public function __construct($periode, $data, $title)
    {
        $this->periode = $periode;
        $this->data = $data;
        $this->title = $title;
        $this->view();
    }

    public function view()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

        $sheet->setCellValue('A1', $this->title);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1:I3')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:I1')->getFont()
            ->setBold(true)
            ->setSize(15);
        $row = 3;

        $sheet->mergeCells('A' . $row . ':B' . $row)->setCellValue('A' . $row, 'KODE');
        $sheet->setCellValue('C' . $row, 'URAIAN');
        $sheet->setCellValue('D' . $row, 'SALDO AWAL DEBIT');
        $sheet->setCellValue('E' . $row, 'SALDO AWAL KREDIT');
        $sheet->setCellValue('F' . $row, 'PERUBAHAN DEBIT');
        $sheet->setCellValue('G' . $row, 'PERUBAHAN KREDIT');
        $sheet->setCellValue('H' . $row, 'SALDO AKHIR DEBIT');
        $sheet->setCellValue('I' . $row, 'SALDO AKHIR KREDIT');

        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);

        $sheet->getStyle('A' . $row . ':' . 'I' . $row)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $row++;
        $no = 1;

        foreach ($this->data as $account) {
            if ($account->children()->count()) {
                $sheet->getStyle('A' . $row . ':' . 'I' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('efefef');
                $sheet->getStyle('A' . $row . ':' . 'I' . $row)->applyFromArray($this->fontBold);
                $sheet->mergeCells('A' . $row . ':B' . $row);
                $sheet->setCellValue('A' . $row, $account->kode);
            } else {
                $sheet->setCellValue('B' . $row, $account->kode);
            }

            $sheet->setCellValue('C' . $row, $account->uraian);
            $sheet->setCellValue('D' . $row, $account->saldo_awal_debit);
            $sheet->setCellValue('E' . $row, $account->saldo_awal_kredit);
            $sheet->setCellValue('F' . $row, $account->perubahan_debit);
            $sheet->setCellValue('G' . $row, $account->perubahan_kredit);
            $sheet->setCellValue('H' . $row, $account->saldo_akhir_debit);
            $sheet->setCellValue('I' . $row, $account->saldo_akhir_kredit);

            $sheet->getCell('D' . $row)->getCalculatedValue();
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('E' . $row)->getCalculatedValue();
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('F' . $row)->getCalculatedValue();
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('G' . $row)->getCalculatedValue();
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('H' . $row)->getCalculatedValue();
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');
            $sheet->getCell('I' . $row)->getCalculatedValue();
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('\Rp\. #,##0');

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
