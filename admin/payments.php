<?php
// admin/payments.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all payments with student and course details
$sql = "SELECT p.payment_id, p.transaction_id, p.amount, p.payment_method, p.status, p.created_at,
               s.student_id, u.first_name, u.last_name, u.email, c.course_name
        FROM payments p
        JOIN students s ON p.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN courses c ON s.course_id = c.course_id
        ORDER BY p.created_at DESC";
$payments = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Manage Payments</h1>

        <!-- Payment List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Student Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Course</th>
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
                            <td class="px-4 py-2"><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($payment['email']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($payment['course_name'] ?? 'N/A') ?></td>
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
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>