<?php
// student/dashboard.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.student_id, s.admission_status, s.admission_date, s.payment_status, s.course_id,
               u.first_name, u.last_name, u.email, u.phone, c.course_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN courses c ON s.course_id = c.course_id
        WHERE s.user_id = ?";
$student = fetchSingle($sql, [$user_id]);

// Fetch payment details
$sql = "SELECT p.payment_id, p.transaction_id, p.amount, p.payment_method, p.status, p.created_at
        FROM payments p
        WHERE p.student_id = ?
        ORDER BY p.created_at DESC";
$payments = fetchAll($sql, [$student['student_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Student Dashboard</h1>

        <!-- Student Details -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Your Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-700"><strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></p>
                    <p class="text-gray-700"><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                    <p class="text-gray-700"><strong>Phone:</strong> <?= htmlspecialchars($student['phone']) ?></p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Course:</strong> <?= htmlspecialchars($student['course_name'] ?? 'N/A') ?></p>
                    <p class="text-gray-700"><strong>Admission Status:</strong>
                        <span class="px-2 py-1 rounded 
                            <?= $student['admission_status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($student['admission_status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                            <?= ucfirst($student['admission_status']) ?>
                        </span>
                    </p>
                    <p class="text-gray-700"><strong>Payment Status:</strong>
                        <span class="px-2 py-1 rounded 
                            <?= $student['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                               ($student['payment_status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= ucfirst($student['payment_status']) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Payment History</h2>
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Payment Method</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Transaction ID</th>
                        <th class="px-4 py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2">₹<?= number_format($payment['amount'], 2) ?></td>
                            <td class="px-4 py-2"><?= ucfirst($payment['payment_method']) ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded 
                                    <?= $payment['status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                       ($payment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2"><?= htmlspecialchars($payment['transaction_id']) ?></td>
                            <td class="px-4 py-2"><?= date('d M Y, h:i A', strtotime($payment['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Links -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Quick Links</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="admission_form.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Admission Form</a>
                <a href="payment.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Make Payment</a>
                <a href="documents.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Upload Documents</a>
                <a href="profile.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Update Profile</a>
                <a href="status.php" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 text-center">Admission Status</a>
                <a href="../auth/logout.php" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 text-center">Logout</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>