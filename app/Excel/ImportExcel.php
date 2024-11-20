<?php

namespace App\Excel;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportExcel
{
    private $spreadsheet;
    private $reader;

    public function __construct($filename)
    {
        $this->reader = new Xlsx();
        $this->spreadsheet = $this->reader->load($filename);
    }

    public function toArray(array $keysToSkip = [0])
    {
        $data = $this->spreadsheet->getActiveSheet()->toArray();
        foreach ($keysToSkip as $key) {
            unset($data[$key]);
        }
        return array_values($data);
    }
}
