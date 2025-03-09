<?php
// includes/header.php

session_start();
?>

<header class="bg-blue-600 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="../index.php" class="text-2xl font-bold">College Admission System</a>
        <nav>
            <ul class="flex space-x-4">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php" class="hover:text-blue-300">Dashboard</a></li>
                        <li><a href="admin/students.php" class="hover:text-blue-300">Students</a></li>
                        <li><a href="admin/payments.php" class="hover:text-blue-300">Payments</a></li>
                    <?php else: ?>
                        <li><a href="student/dashboard.php" class="hover:text-blue-300">Dashboard</a></li>
                        <li><a href="student/admission_form.php" class="hover:text-blue-300">Admission Form</a></li>
                    <?php endif; ?>
                    <li><a href="auth/logout.php" class="hover:text-blue-300">Logout</a></li>
                <?php else: ?>
                    <li><a href="auth/login.php" class="hover:text-blue-300">Login</a></li>
                    <li><a href="auth/register.php" class="hover:text-blue-300">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>