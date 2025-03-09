<?php
// student/payment.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.student_id, s.course_id, c.fees
        FROM students s
        LEFT JOIN courses c ON s.course_id = c.course_id
        WHERE s.user_id = ?";
$student = fetchSingle($sql, [$user_id]);

// Handle payment status messages
$status = $_GET['status'] ?? '';
$message = '';
if ($status === 'success') {
    $message = 'Payment successful!';
} elseif ($status === 'failed') {
    $message = 'Payment failed. Please try again.';
} elseif ($status === 'invalid') {
    $message = 'Invalid payment request.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Make Payment</h1>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Payment Details -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-bold mb-4">Payment Details</h2>
            <p class="text-gray-700"><strong>Course:</strong> <?= htmlspecialchars($student['course_name'] ?? 'N/A') ?></p>
            <p class="text-gray-700"><strong>Total Fees:</strong> ₹<?= number_format($student['fees'], 2) ?></p>
        </div>

        <!-- Razorpay Payment Button -->
        <button id="rzp-button" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Pay Now</button>
    </div>

    <!-- Razorpay Script -->
    <script>
        const options = {
            key: 'YOUR_RAZORPAY_KEY_ID', // Replace with your Razorpay Key ID
            amount: <?= $student['fees'] * 100 ?>, // Amount in paise (e.g., 500000 for ₹5000.00)
            currency: 'INR',
            name: 'College Admission System',
            description: 'Course Fees Payment',
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
                amount.value = <?= $student['fees'] * 100 ?>;
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