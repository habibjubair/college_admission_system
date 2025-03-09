<?php
// includes/csrf.php

session_start();

// Generate a CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get the current CSRF token.
 */
function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token.
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}