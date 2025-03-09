<?php
// payments/razorpay/webhook.php

require_once '../../includes/db.php';

// Verify the webhook signature
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
$webhook_secret = 'YOUR_RAZORPAY_WEBHOOK_SECRET';

if (hash_hmac('sha256', $payload, $webhook_secret) === $signature) {
    $data = json_decode($payload, true);

    // Handle payment success event
    if ($data['event'] === 'payment.captured') {
        $payment_id = $data['payload']['payment']['entity']['id'];
        $amount = $data['payload']['payment']['entity']['amount'] / 100; // Convert to rupees
        $status = $data['payload']['payment']['entity']['status'];

        // Update payment status in the database
        $sql = "UPDATE payments SET status = ? WHERE transaction_id = ?";
        executeQuery($sql, [$status, $payment_id]);

        // Send payment success email
        $student_id = fetchSingle("SELECT student_id FROM payments WHERE transaction_id = ?", [$payment_id])['student_id'];
        $student_email = fetchSingle("SELECT u.email FROM students s JOIN users u ON s.user_id = u.user_id WHERE s.student_id = ?", [$student_id])['email'];
        sendPaymentSuccessEmail($student_email, $amount);
    }

    http_response_code(200);
} else {
    http_response_code(400);
}