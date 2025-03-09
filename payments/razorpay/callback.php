<?php
// payments/razorpay/callback.php

require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Ensure the user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.student_id FROM students s WHERE s.user_id = ?";
$student = fetchSingle($sql, [$user_id]);

// Handle Razorpay payment response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $razorpay_order_id = $_POST['razorpay_order_id'];
    $razorpay_signature = $_POST['razorpay_signature'];

    // Verify the payment signature
    $generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, 'YOUR_RAZORPAY_KEY_SECRET');

    if ($generated_signature === $razorpay_signature) {
        // Payment is successful
        $amount = $_POST['amount']; // Amount in paise (e.g., 500000 for ₹5000.00)
        $amount_in_rupees = $amount / 100;

        // Insert payment details into the database
        $sql = "INSERT INTO payments (student_id, transaction_id, amount, payment_method, status) VALUES (?, ?, ?, 'razorpay', 'success')";
        executeQuery($sql, [$student['student_id'], $razorpay_payment_id, $amount_in_rupees]);

        // Redirect to payment success page
        header('Location: ../../student/payment.php?status=success');
        exit();
    } else {
        // Payment failed
        header('Location: ../../student/payment.php?status=failed');
        exit();
    }
} else {
    // Invalid request
    header('Location: ../../student/payment.php?status=invalid');
    exit();
}