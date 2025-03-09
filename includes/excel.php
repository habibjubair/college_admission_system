<?php
// includes/excel.php

require 'path/to/vendor/autoload.php'; // Include PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function generateExcel($data, $filename) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add headers
    $headers = array_keys($data[0]);
    $sheet->fromArray($headers, NULL, 'A1');

    // Add data rows
    $rowData = [];
    foreach ($data as $row) {
        $rowData[] = array_values($row);
    }
    $sheet->fromArray($rowData, NULL, 'A2');

    // Save the Excel file
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}