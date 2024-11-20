<?php

namespace App\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NeracaExcel extends ReportExcel
{
    private $spreadsheet;
    private $sheet;
    private $periode;
    private $data;
    private $title;
    private $alphabet = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
        'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
        'U', 'V', 'W', 'X', 'Y', 'Z'
    ];

    public function __construct($periode, $data, $title)
    {
        $this->periode = $periode;
        $this->data = $data;
        $this->title = $title;

        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->view();
    }

    public function view()
    {
        $this->setDefault();
        $this->setTitle();

        $this->setHeader();

        $row = 5; // Start from the first row

        $no = 0;  // Initial value for $no
        $V1 = $this->insertRow($row, $no, $this->data[0], 'A');
        $row = $V1[0];
        $row = $this->insertTotalRow('Jumlah Aset (A)', $row, $this->data[0]);

        $no = 0;
        $V2 = $this->insertRow($row, $no, $this->data[1], 'B');
        $row = $V2[0];
        $row = $this->insertTotalRow('Jumlah Kewajiban (B)', $row, $this->data[1]);

        $no = 0;
        $V2 = $this->insertRow($row, $no, $this->data[2], 'C');
        $row = $V2[0];
        $row = $this->insertTotalRow('Jumlah Ekuitas (C)', $row, $this->data[2]);

        // set column size
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($columns as $colum) {
            $this->sheet->getColumnDimension($colum)->setAutoSize(true);
        }
    }

    private function insertTotalRow($title, $row, $value)
    {
        $this->sheet->setCellValue('A' . $row, '');
        $this->sheet->setCellValue('B' . $row, $title);
        $this->sheet->setCellValue('C' . $row, $value['end'])
            ->getStyle('C' . $row)->getNumberFormat()->setFormatCode('@');
        $this->sheet->setCellValue('D' . $row, $value['start'])
            ->getStyle('D' . $row)->getNumberFormat()->setFormatCode('@');
        $this->sheet->setCellValue('E' . $row, $value['selisih'])
            ->getStyle('E' . $row)->getNumberFormat()->setFormatCode('@');
        $this->sheet->setCellValue('F' . $row, $value['persen'])
            ->getStyle('F' . $row)->getNumberFormat()->setFormatCode('@');

        $this->sheet->getStyle("A$row:F$row")->getFont()->setBold(true);

        return $row++;
    }

    private function insertRow($row, $no, $value, $group)
    {
        $number = $no == 0 ? $group : "$group.$no";

        $row++;
        $no++;
        // Set values for the current row
        $this->sheet->setCellValue('A' . $row, $number);
        $this->sheet->setCellValue('B' . $row, "  " . $value['uraian']);
        $this->sheet->setCellValue('C' . $row, $value['end'])
            ->getStyle('C' . $row)->getNumberFormat()->setFormatCode('@');
        $this->sheet->setCellValue('D' . $row, $value['start'])
            ->getStyle('C' . $row)->getNumberFormat()->setFormatCode('@');
        $this->sheet->setCellValue('E' . $row, $value['selisih'])
            ->getStyle('C' . $row)->getNumberFormat()->setFormatCode('@');
        $this->sheet->setCellValue('F' . $row, $value['persen'])
            ->getStyle('C' . $row)->getNumberFormat()->setFormatCode('@');

        // Check if $value has children
        if (isset($value['children']) && is_array($value['children'])) {
            // Recursively set values for each child
            foreach ($value['children'] as $child) {
                $h = $this->insertRow($row, $no, $child, $group);
                $row = $h[0];
                $no = $h[1];
            }
        }
        return [$row, $no];
    }

    private function setHeader()
    {
        $this->sheet->setCellValue('E4', 'Kenaikan (Penurunan)');
        $this->sheet->mergeCells('E4:F4');

        $this->sheet->mergeCells('A4:A5');

        $this->sheet->setCellValue('B4', 'Uraian');
        $this->sheet->mergeCells('B4:B5');
        $this->sheet->setCellValue('C4', $this->periode['start']);
        $this->sheet->mergeCells('C4:C5');
        $this->sheet->setCellValue('D4', $this->periode['end']);
        $this->sheet->mergeCells('D4:D5');

        $this->sheet->setCellValue('E5', 'Jumlah');
        $this->sheet->setCellValue('F5', '%');
    }

    private function setTitle()
    {
        $this->sheet->setCellValue('A1', 'Distrik Navigasi Tipe B Tanjung Priok');
        $this->sheet->mergeCells('A1:F1');

        $this->sheet->setCellValue('A2', 'NERACA');
        $this->sheet->mergeCells('A2:F2');

        $this->sheet->setCellValue('A3', $this->title);
        $this->sheet->mergeCells('A3:F3');

        $this->sheet->getStyle('A1:F5')->getAlignment()->setVertical('center');
        $this->sheet->getStyle('A1:F5')->getAlignment()->setHorizontal('center');

        $this->sheet->getStyle('A1:F5')->getFont()
            ->setBold(true);
    }

    private function setDefault()
    {
        $this->spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $this->spreadsheet->getDefaultStyle()->getFont()->setSize(12);
    }

    public function download($filename = "")
    {
        $writer = new Xlsx($this->spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        $writer->save("php://output");
    }
}
