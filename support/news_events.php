<?php
// support/news_events.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Fetch news and events from the database
$sql = "SELECT * FROM news_events ORDER BY event_date DESC";
$news_events = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">News & Events</h1>

        <!-- News & Events List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <?php foreach ($news_events as $event): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold"><?= htmlspecialchars($event['title']) ?></h2>
                    <p class="text-gray-700"><?= htmlspecialchars($event['description']) ?></p>
                    <p class="text-gray-500 text-sm">Event Date: <?= date('d M Y', strtotime($event['event_date'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>