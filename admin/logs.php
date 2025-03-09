<?php
// admin/logs.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all audit logs with admin details
$sql = "SELECT l.log_id, l.action, l.details, l.created_at,
               a.admin_id, u.first_name, u.last_name, u.email
        FROM audit_logs l
        JOIN admins a ON l.admin_id = a.admin_id
        JOIN users u ON a.user_id = u.user_id
        ORDER BY l.created_at DESC";
$logs = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Audit Logs</h1>

        <!-- Logs Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Admin Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Action</th>
                        <th class="px-4 py-2">Details</th>
                        <th class="px-4 py-2">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($log['email']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($log['action']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($log['details']) ?></td>
                            <td class="px-4 py-2"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></td>
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