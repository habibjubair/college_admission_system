<?php
// includes/pdf.php

require('path/to/fpdf.php'); // Include the FPDF library

function generatePDF($data, $filename) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Add a title
    $pdf->Cell(40, 10, ucfirst($filename));

    // Add headers
    $pdf->SetFont('Arial', 'B', 12);
    $headers = array_keys($data[0]);
    foreach ($headers as $header) {
        $pdf->Cell(40, 10, ucfirst(str_replace('_', ' ', $header)), 1);
    }
    $pdf->Ln();

    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    foreach ($data as $row) {
        foreach ($row as $cell) {
            $pdf->Cell(40, 10, $cell, 1);
        }
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('D', $filename . '.pdf');
    exit();
}