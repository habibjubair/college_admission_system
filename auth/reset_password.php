<?php
// auth/reset_password.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_GET['token']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($token)) {
        $error = 'Invalid reset token.';
    } elseif (empty($password) || empty($confirm_password)) {
        $error = 'Password and confirm password are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if the token is valid and not expired
        $sql = "SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW()";
        $user = fetchSingle($sql, [$token]);

        if ($user) {
            // Hash the new password
            $password_hash = hashPassword($password);

            // Update the user's password and clear the reset token
            $sql = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE user_id = ?";
            executeQuery($sql, [$password_hash, $user['user_id']]);

            $success = 'Password reset successfully! You can now <a href="login.php">login</a>.';
        } else {
            $error = 'Invalid or expired reset token.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center">Reset Password</h1>
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
                <label for="password" class="block text-gray-700">New Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 border rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="w-full px-3 py-2 border rounded-lg" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Reset Password</button>
        </form>
        <p class="text-center mt-4"><a href="login.php" class="text-blue-500">Back to Login</a></p>
    </div>
</body>
</html>