<?php
// index.php
session_start();
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Admission System</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center">Welcome to the College Admission System</h1>
        <p class="text-center mt-4">Please <a href="auth/login.php" class="text-blue-500">login</a> or <a href="auth/register.php" class="text-blue-500">register</a> to continue.</p>
    </div>
</body>
</html>

<?php
require_once 'includes/footer.php';
?>