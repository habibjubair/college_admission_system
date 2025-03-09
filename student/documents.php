<?php
// student/documents.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.student_id FROM students s WHERE s.user_id = ?";
$student = fetchSingle($sql, [$user_id]);

// Fetch uploaded documents
$sql = "SELECT documents FROM admission_form WHERE student_id = ?";
$form = fetchSingle($sql, [$student['student_id']]);
$documents = $form ? json_decode($form['documents'], true) : [];

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Upload Aadhar card
    if (!empty($_FILES['aadhar']['name'])) {
        $aadhar_file = $upload_dir . basename($_FILES['aadhar']['name']);
        move_uploaded_file($_FILES['aadhar']['tmp_name'], $aadhar_file);
        $documents['aadhar'] = $_FILES['aadhar']['name'];
    }

    // Upload Marksheet
    if (!empty($_FILES['marksheet']['name'])) {
        $marksheet_file = $upload_dir . basename($_FILES['marksheet']['name']);
        move_uploaded_file($_FILES['marksheet']['tmp_name'], $marksheet_file);
        $documents['marksheet'] = $_FILES['marksheet']['name'];
    }

    // Update documents in the database
    $documents_json = json_encode($documents);
    $sql = "UPDATE admission_form SET documents = ? WHERE student_id = ?";
    executeQuery($sql, [$documents_json, $student['student_id']]);

    // Redirect to avoid form resubmission
    header('Location: documents.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Upload Documents</h1>

        <!-- Upload Form -->
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="aadhar" class="block text-gray-700">Aadhar Card</label>
                    <input type="file" name="aadhar" id="aadhar" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label for="marksheet" class="block text-gray-700">Marksheet</label>
                    <input type="file" name="marksheet" id="marksheet" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 mt-4">Upload</button>
        </form>

        <!-- Uploaded Documents -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Uploaded Documents</h2>
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Document</th>
                        <th class="px-4 py-2">File Name</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $type => $file): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= ucfirst($type) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($file) ?></td>
                            <td class="px-4 py-2">
                                <a href="../uploads/<?= htmlspecialchars($file) ?>" target="_blank" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">View</a>
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