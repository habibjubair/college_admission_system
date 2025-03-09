<?php
// student/status.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.student_id, s.admission_status, s.payment_status, s.course_id, c.fees
        FROM students s
        LEFT JOIN courses c ON s.course_id = c.course_id
        WHERE s.user_id = ?";
$student = fetchSingle($sql, [$user_id]);

// Fetch payment details
$sql = "SELECT p.payment_id, p.transaction_id, p.amount, p.status, p.created_at
        FROM payments p
        WHERE p.student_id = ?
        ORDER BY p.created_at DESC";
$payments = fetchAll($sql, [$student['student_id']]);

// Handle payment status messages
$status = $_GET['status'] ?? '';
$message = '';
if ($status === 'success') {
    $message = 'Payment successful! Your admission status is now pending approval.';
} elseif ($status === 'failed') {
    $message = 'Payment failed. Please try again.';
} elseif ($status === 'invalid') {
    $message = 'Invalid payment request.';
}

// Check if admission fee is paid
$admission_fee_paid = false;
foreach ($payments as $payment) {
    if ($payment['status'] === 'success' && $payment['amount'] >= 500) {
        $admission_fee_paid = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Status - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Admission Status</h1>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Admission Status -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Your Admission Status</h2>
            <p class="text-gray-700"><strong>Admission Status:</strong>
                <span class="px-2 py-1 rounded 
                    <?= $student['admission_status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                       ($student['admission_status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                    <?= ucfirst($student['admission_status']) ?>
                </span>
            </p>
            <p class="text-gray-700"><strong>Payment Status:</strong>
                <span class="px-2 py-1 rounded 
                    <?= $admission_fee_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= $admission_fee_paid ? 'Paid' : 'Pending' ?>
                </span>
            </p>
        </div>

        <!-- Payment Section -->
        <?php if (!$admission_fee_paid): ?>
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-bold mb-4">Pay Admission Fee</h2>
                <p class="text-gray-700 mb-4">To complete your admission, please pay the admission fee of <strong>₹500</strong>.</p>
                <button id="rzp-button" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Pay Now</button>
            </div>
        <?php endif; ?>

        <!-- Payment History -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Payment History</h2>
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Transaction ID</th>
                        <th class="px-4 py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2">₹<?= number_format($payment['amount'], 2) ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded 
                                    <?= $payment['status'] === 'success' ? 'bg-green-100 text-green-800' : 
                                       ($payment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2"><?= htmlspecialchars($payment['transaction_id']) ?></td>
                            <td class="px-4 py-2"><?= date('d M Y, h:i A', strtotime($payment['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Razorpay Script -->
    <script>
        const options = {
            key: 'YOUR_RAZORPAY_KEY_ID', // Replace with your Razorpay Key ID
            amount: 50000, // Amount in paise (₹500)
            currency: 'INR',
            name: 'College Admission System',
            description: 'Admission Fee Payment',
            image: '../assets/images/logo.png', // Replace with your logo
            order_id: '<?= uniqid() ?>', // Replace with your order ID logic
            handler: function (response) {
                // Submit the payment response to the callback URL
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../payments/razorpay/callback.php';

                const paymentId = document.createElement('input');
                paymentId.type = 'hidden';
                paymentId.name = 'razorpay_payment_id';
                paymentId.value = response.razorpay_payment_id;
                form.appendChild(paymentId);

                const orderId = document.createElement('input');
                orderId.type = 'hidden';
                orderId.name = 'razorpay_order_id';
                orderId.value = response.razorpay_order_id;
                form.appendChild(orderId);

                const signature = document.createElement('input');
                signature.type = 'hidden';
                signature.name = 'razorpay_signature';
                signature.value = response.razorpay_signature;
                form.appendChild(signature);

                const amount = document.createElement('input');
                amount.type = 'hidden';
                amount.name = 'amount';
                amount.value = 50000; // ₹500 in paise
                form.appendChild(amount);

                document.body.appendChild(form);
                form.submit();
            },
            prefill: {
                name: '<?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?>',
                email: '<?= $_SESSION['email'] ?>',
                contact: '<?= $_SESSION['phone'] ?>',
            },
            theme: {
                color: '#3399cc',
            },
        };

        const rzp = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function (e) {
            rzp.open();
            e.preventDefault();
        };
    </script>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>