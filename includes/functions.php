<?php
// includes/functions.php

/**
 * Sanitize input data to prevent XSS attacks.
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific URL.
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Generate a random token for CSRF protection.
 */
function generateCsrfToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Validate a CSRF token.
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format a date for display.
 */
function formatDate($date, $format = 'd M Y, h:i A') {
    return date($format, strtotime($date));
}

/**
 * Send an email using PHP's mail() function.
 */
function sendEmail($to, $subject, $message, $headers = []) {
    $defaultHeaders = [
        'From' => 'no-reply@collegeadmission.com',
        'Reply-To' => 'no-reply@collegeadmission.com',
        'X-Mailer' => 'PHP/' . phpversion(),
        'Content-Type' => 'text/html; charset=UTF-8',
    ];
    $headers = array_merge($defaultHeaders, $headers);

    return mail($to, $subject, $message, $headers);
}