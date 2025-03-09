<?php
// backups/restore_db.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $backup_file = $_POST['backup_file'];

    // Database credentials
    $host = 'localhost';
    $username = 'root'; // Replace with your database username
    $password = ''; // Replace with your database password
    $database = 'college_admission_system';

    // Restore command
    $command = "mysql --user={$username} --password={$password} --host={$host} {$database} < {$backup_file}";

    // Execute the restore command
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        // Redirect with success message
        header('Location: ../admin/dashboard.php?restore=success');
        exit();
    } else {
        // Redirect with error message
        header('Location: ../admin/dashboard.php?restore=failed');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Database - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Restore Database</h1>

        <!-- Backup Files List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Select a Backup File</h2>
            <form method="POST">
                <select name="backup_file" class="w-full px-3 py-2 border rounded-lg mb-4">
                    <?php
                    $backup_files = glob('../backups/*.sql');
                    foreach ($backup_files as $file) {
                        echo "<option value='{$file}'>" . basename($file) . "</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Restore</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>