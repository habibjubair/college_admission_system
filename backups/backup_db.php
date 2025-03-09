<?php
// backups/backup_db.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Database credentials
$host = 'localhost';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password
$database = 'college_admission_system';

// Backup file name and path
$backup_file = '../backups/backup-' . date('Y-m-d-H-i-s') . '.sql';
$command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$backup_file}";

// Execute the backup command
exec($command, $output, $return_var);

if ($return_var === 0) {
    // Log the backup in the database
    $sql = "INSERT INTO backups (file_path) VALUES (?)";
    executeQuery($sql, [$backup_file]);

    // Redirect with success message
    header('Location: ../admin/dashboard.php?backup=success');
    exit();
} else {
    // Redirect with error message
    header('Location: ../admin/dashboard.php?backup=failed');
    exit();
}