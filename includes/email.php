<?php
// includes/email.php

require_once 'mailer.php';

/**
 * Send an admission confirmation email.
 */
function sendAdmissionConfirmationEmail($to, $student_name) {
    $subject = 'Admission Confirmation';
    $message = file_get_contents('../notifications/email_templates/admission_confirmation.html');
    $message = str_replace('{{student_name}}', $student_name, $message);

    return sendEmail($to, $subject, $message);
}

/**
 * Send a payment success email.
 */
function sendPaymentSuccessEmail($to, $amount) {
    $subject = 'Payment Successful';
    $message = file_get_contents('../notifications/email_templates/payment_success.html');
    $message = str_replace('{{amount}}', $amount, $message);

    return sendEmail($to, $subject, $message);
}

/**
 * Send a password reset email.
 */
function sendPasswordResetEmail($to, $reset_link) {
    $subject = 'Password Reset Request';
    $message = file_get_contents('../notifications/email_templates/password_reset.html');
    $message = str_replace('{{reset_link}}', $reset_link, $message);

    return sendEmail($to, $subject, $message);
}

/**
 * Send a generic email.
 */
function sendGenericEmail($to, $subject, $template, $placeholders = []) {
    $message = file_get_contents("../notifications/email_templates/{$template}.html");
    foreach ($placeholders as $key => $value) {
        $message = str_replace("{{{$key}}}", $value, $message);
    }

    return sendEmail($to, $subject, $message);
}