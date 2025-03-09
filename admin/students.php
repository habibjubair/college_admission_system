<?php
// admin/students.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle admission status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) {
    $student_id = intval($_POST['student_id']);
    $action = $_POST['action'];

    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $sql = "UPDATE students SET admission_status = ? WHERE student_id = ?";
        executeQuery($sql, [$status, $student_id]);

        // Redirect to avoid form resubmission
        header('Location: students.php');
        exit();
    }
}

// Fetch all students with their details
$sql = "SELECT s.student_id, s.admission_status, s.admission_date, s.payment_status, 
               u.first_name, u.last_name, u.email, u.phone, c.course_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN courses c ON s.course_id = c.course_id
        ORDER BY s.admission_status, s.admission_date DESC";
$students = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Manage Students</h1>

        <!-- Student List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Course</th>
                        <th class="px-4 py-2">Admission Status</th>
                        <th class="px-4 py-2">Payment Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($student['email']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($student['phone']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($student['course_name'] ?? 'N/A') ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded 
                                    <?= $student['admission_status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($student['admission_status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                    <?= ucfirst($student['admission_status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded 
                                    <?= $student['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($student['payment_status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst($student['payment_status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <?php if ($student['admission_status'] === 'pending'): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                        <button type="submit" name="action" value="approve" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">Approve</button>
                                        <button type="submit" name="action" value="reject" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
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