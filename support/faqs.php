<?php
// support/faqs.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Fetch FAQs from the database
$sql = "SELECT * FROM faqs ORDER BY created_at DESC";
$faqs = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Frequently Asked Questions</h1>

        <!-- FAQs List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <?php foreach ($faqs as $faq): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold"><?= htmlspecialchars($faq['question']) ?></h2>
                    <p class="text-gray-700"><?= htmlspecialchars($faq['answer']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>