<?php
// admin/reports.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle report generation requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_type = $_POST['report_type'];
    $format = $_POST['format'];

    if ($report_type === 'students') {
        // Fetch student data
        $sql = "SELECT s.student_id, s.admission_status, s.admission_date, s.payment_status,
                       u.first_name, u.last_name, u.email, u.phone, c.course_name
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                LEFT JOIN courses c ON s.course_id = c.course_id
                ORDER BY s.admission_date DESC";
        $data = fetchAll($sql);
        $filename = 'students_report';
    } elseif ($report_type === 'payments') {
        // Fetch payment data
        $sql = "SELECT p.payment_id, p.transaction_id, p.amount, p.payment_method, p.status, p.created_at,
                       s.student_id, u.first_name, u.last_name, u.email, c.course_name
                FROM payments p
                JOIN students s ON p.student_id = s.student_id
                JOIN users u ON s.user_id = u.user_id
                LEFT JOIN courses c ON s.course_id = c.course_id
                ORDER BY p.created_at DESC";
        $data = fetchAll($sql);
        $filename = 'payments_report';
    }

    // Generate the report
    if ($format === 'pdf') {
        require_once '../includes/pdf.php';
        generatePDF($data, $filename);
    } elseif ($format === 'excel') {
        require_once '../includes/excel.php';
        generateExcel($data, $filename);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Generate Reports</h1>

        <!-- Report Form -->
        <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
            <form method="POST">
                <div class="mb-4">
                    <label for="report_type" class="block text-gray-700">Report Type</label>
                    <select name="report_type" id="report_type" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="students">Students</option>
                        <option value="payments">Payments</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="format" class="block text-gray-700">Format</label>
                    <select name="format" id="format" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Generate Report</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>