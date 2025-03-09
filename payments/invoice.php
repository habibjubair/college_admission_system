<?php
// payments/invoice.php

require_once '../includes/db.php';
require_once '../includes/functions.php';
require('path/to/fpdf.php'); // Include FPDF library

function generateInvoice($payment_id) {
    global $pdo;

    // Fetch payment details
    $sql = "SELECT p.*, u.first_name, u.last_name, u.email, c.course_name
            FROM payments p
            JOIN students s ON p.student_id = s.student_id
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN courses c ON s.course_id = c.course_id
            WHERE p.payment_id = ?";
    $payment = fetchSingle($sql, [$payment_id]);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Add invoice title
    $pdf->Cell(40, 10, 'Invoice');

    // Add payment details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Payment ID: ' . $payment['payment_id']);
    $pdf->Cell(40, 10, 'Student Name: ' . $payment['first_name'] . ' ' . $payment['last_name']);
    $pdf->Cell(40, 10, 'Course: ' . $payment['course_name']);
    $pdf->Cell(40, 10, 'Amount: ₹' . number_format($payment['amount'], 2));
    $pdf->Cell(40, 10, 'Date: ' . formatDate($payment['created_at']));

    // Save the PDF
    $invoice_path = '../invoices/invoice_' . $payment_id . '.pdf';
    $pdf->Output('F', $invoice_path);

    return $invoice_path;
}