<?php
// includes/auth.php

session_start();

// Function to hash passwords
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify passwords
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if the logged-in user is an admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to check if the logged-in user is a student
function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Function to log in a user
function login($email, $password) {
    global $pdo;

    // Fetch user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $user = fetchSingle($sql, [$email]);

    if ($user && verifyPassword($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        // Update last login time
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        executeQuery($sql, [$user['user_id']]);

        return true;
    }

    return false;
}

// Function to log out a user
function logout() {
    session_unset();
    session_destroy();
}

// Function to register a new user
function registerUser($email, $password, $first_name, $last_name, $phone, $role = 'student') {
    global $pdo;

    // Check if the email already exists
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $existingUser = fetchSingle($sql, [$email]);

    if ($existingUser) {
        return false; // Email already exists
    }

    // Hash the password
    $password_hash = hashPassword($password);

    // Insert the new user
    $sql = "INSERT INTO users (email, password_hash, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = executeQuery($sql, [$email, $password_hash, $first_name, $last_name, $phone, $role]);

    return $stmt->rowCount() > 0;
}