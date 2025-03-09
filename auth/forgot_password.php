<?php
// auth/forgot_password.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Validate email
    if (empty($email)) {
        $error = 'Email is required.';
    } else {
        // Check if the email exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $user = fetchSingle($sql, [$email]);

        if ($user) {
            // Generate a password reset token (for simplicity, use a random string)
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save the token in the database
            $sql = "UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE user_id = ?";
            executeQuery($sql, [$token, $expires_at, $user['user_id']]);

            // Send the reset link via email (placeholder for now)
            $reset_link = "http://yourdomain.com/auth/reset_password.php?token=$token";
            $success = "A password reset link has been sent to your email.";
        } else {
            $error = 'Email not found.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center">Forgot Password</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Reset Password</button>
        </form>
        <p class="text-center mt-4"><a href="login.php" class="text-blue-500">Back to Login</a></p>
    </div>
</body>
</html>