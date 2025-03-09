<?php
// includes/mailer.php

require '../vendor/autoload.php'; // Load Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using PHPMailer.
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML or plain text)
 * @param array $attachments Optional array of file paths to attach
 * @return bool True if the email was sent successfully, false otherwise
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Replace with your email
        $mail->Password = 'your-email-password'; // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('no-reply@collegeadmission.com', 'College Admission System');
        $mail->addAddress($to); // Add a recipient

        // Attachments
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment); // Add attachments
        }

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}


//Using the Mailer System
//Now, you can use the sendEmail function or the specific email functions (sendAdmissionConfirmationEmail, sendPaymentSuccessEmail, etc.) anywhere in your application.//

//Example: Sending an Admission Confirmation Email
//
//uncomment all below code
//<?php
//require_once 'includes/email.php';

//$to = 'student@example.com';
//$student_name = 'John Doe';

//if (sendAdmissionConfirmationEmail($to, $student_name)) {
  //  echo 'Admission confirmation email sent successfully!';
//} else {
  //  echo 'Failed to send admission confirmation email.';
//}
//Step 20.6: Testing the Mailer System
//Update the SMTP settings in mailer.php with your email provider's credentials.
//
//Test sending an email using the sendEmail function or one of the specific email functions.

//Check your email inbox (and spam folder) to confirm receipt.
//