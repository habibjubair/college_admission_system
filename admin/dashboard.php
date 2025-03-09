<?php
// admin/dashboard.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch statistics for the dashboard
$sql = "SELECT COUNT(*) AS total_students FROM students";
$total_students = fetchSingle($sql)['total_students'];

$sql = "SELECT COUNT(*) AS pending_students FROM students WHERE admission_status = 'pending'";
$pending_students = fetchSingle($sql)['pending_students'];

$sql = "SELECT COUNT(*) AS total_payments FROM payments WHERE status = 'success'";
$total_payments = fetchSingle($sql)['total_payments'];

$sql = "SELECT SUM(amount) AS total_revenue FROM payments WHERE status = 'success'";
$total_revenue = fetchSingle($sql)['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Admin Dashboard</h1>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Students Card -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Students</h2>
                <p class="text-3xl"><?= $total_students ?></p>
            </div>

            <!-- Pending Students Card -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Pending Admissions</h2>
                <p class="text-3xl"><?= $pending_students ?></p>
            </div>

            <!-- Total Payments Card -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Payments</h2>
                <p class="text-3xl"><?= $total_payments ?></p>
            </div>

            <!-- Total Revenue Card -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Revenue</h2>
                <p class="text-3xl">₹<?= number_format($total_revenue, 2) ?></p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Quick Links</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="students.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Manage Students</a>
                <a href="payments.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">View Payments</a>
                <a href="reports.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Generate Reports</a>
                <a href="settings.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Admin Settings</a>
                <a href="logs.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">View Logs</a>
                <a href="../auth/logout.php" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 text-center">Logout</a>
            <!-- Add these links to the Quick Links section -->
<a href="../backups/backup_db.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Backup Database</a>
<a href="../backups/restore_db.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Restore Database</a>
            </div>


        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>