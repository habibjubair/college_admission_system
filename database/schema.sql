-- Database: college_admission_system
CREATE DATABASE IF NOT EXISTS `college_admission_system`;
USE `college_admission_system`;

-- Table: users (for both admin and students)
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(15),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_active` TINYINT(1) DEFAULT 1,
    `two_factor_secret` VARCHAR(255), -- For 2FA
    `last_login` TIMESTAMP NULL,
    CONSTRAINT `chk_email_format` CHECK (`email` LIKE '%_@__%.__%')
);

-- Table: admins (additional details for admin users)
CREATE TABLE IF NOT EXISTS `admins` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `role` ENUM('super_admin', 'admission_officer', 'accountant') NOT NULL,
    `permissions` JSON, -- Granular permissions (optional)
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

-- Table: courses (available courses for admission)
CREATE TABLE IF NOT EXISTS `courses` (
    `course_id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `eligibility_criteria` TEXT,
    `duration` VARCHAR(50),
    `fees` DECIMAL(10, 2) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
);

-- Table: students (additional details for student users)
CREATE TABLE IF NOT EXISTS `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `admission_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admission_date` DATE,
    `course_id` INT, -- Linked to courses table
    `payment_status` ENUM('pending', 'paid', 'partial') DEFAULT 'pending',
    `signature` VARCHAR(255), -- E-Signature file path
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE SET NULL
);

-- Table: admission_form (multi-step admission form data)
CREATE TABLE IF NOT EXISTS `admission_form` (
    `form_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `step` TINYINT DEFAULT 1, -- Current step in the multi-step form
    `personal_details` JSON, -- JSON for personal details (name, dob, etc.)
    `academic_details` JSON, -- JSON for academic history
    `documents` JSON, -- JSON for uploaded documents (e.g., {"aadhar": "path/to/file", "marksheet": "path/to/file"})
    `is_complete` TINYINT(1) DEFAULT 0, -- Whether the form is fully submitted
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
);

-- Table: payments (payment details for students)
CREATE TABLE IF NOT EXISTS `payments` (
    `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `transaction_id` VARCHAR(255) NOT NULL, -- Payment gateway transaction ID
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_method` ENUM('razorpay', 'payu', 'upi') NOT NULL,
    `status` ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    `invoice_path` VARCHAR(255), -- Path to generated invoice PDF
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
);

-- Table: notifications (email and SMS notifications)
CREATE TABLE IF NOT EXISTS `notifications` (
    `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('email', 'sms') NOT NULL,
    `subject` VARCHAR(255),
    `message` TEXT NOT NULL,
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

-- Table: audit_logs (logs for admin actions)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `action` VARCHAR(255) NOT NULL,
    `details` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`admin_id`) ON DELETE CASCADE
);

-- Table: support_tickets (support requests from students)
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `ticket_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
);

-- Table: faqs (frequently asked questions)
CREATE TABLE IF NOT EXISTS `faqs` (
    `faq_id` INT AUTO_INCREMENT PRIMARY KEY,
    `question` TEXT NOT NULL,
    `answer` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: news_events (announcements for news and events)
CREATE TABLE IF NOT EXISTS `news_events` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `event_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: backups (database backups)
CREATE TABLE IF NOT EXISTS `backups` (
    `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
    `file_path` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- Triggers for Real-Time Syncing and Automation
-- Trigger: Update student payment status after successful payment
DELIMITER //
CREATE TRIGGER `update_payment_status`
AFTER UPDATE ON `payments`
FOR EACH ROW
BEGIN
    IF NEW.status = 'success' THEN
        UPDATE `students`
        SET `payment_status` = 'paid'
        WHERE `student_id` = NEW.student_id;
    END IF;
END //
DELIMITER ;

-- Trigger: Log admin actions
DELIMITER //
CREATE TRIGGER `log_admin_actions`
AFTER INSERT ON `admins`
FOR EACH ROW
BEGIN
    INSERT INTO `audit_logs` (`admin_id`, `action`, `details`)
    VALUES (NEW.admin_id, 'Admin created', CONCAT('New admin with ID ', NEW.admin_id, ' created.'));
END //
DELIMITER ;

-- Trigger: Auto-generate invoice after payment
DELIMITER //
CREATE TRIGGER `generate_invoice`
AFTER INSERT ON `payments`
FOR EACH ROW
BEGIN
    DECLARE invoice_path VARCHAR(255);
    SET invoice_path = CONCAT('/invoices/', NEW.student_id, '_', NEW.payment_id, '.pdf');
    UPDATE `payments`
    SET `invoice_path` = invoice_path
    WHERE `payment_id` = NEW.payment_id;
END //
DELIMITER ;

-- Indexes for Optimization
CREATE INDEX `idx_user_email` ON `users`(`email`);
CREATE INDEX `idx_student_admission_status` ON `students`(`admission_status`);
CREATE INDEX `idx_payment_status` ON `payments`(`status`);

INSERT INTO `admission_form` (`student_id`, `step`, `personal_details`, `academic_details`, `documents`)
VALUES 
(1, 1, '{"first_name": "John", "last_name": "Doe", "dob": "2000-01-01", "gender": "male", "address": "123 Main St"}', '{}', '{}');
-- Insert a student
INSERT INTO `students` (`user_id`, `admission_status`, `admission_date`, `course_id`, `payment_status`)
VALUES (1, 'pending', '2023-10-01', 1, 'pending');

-- Insert payments
INSERT INTO `payments` (`student_id`, `transaction_id`, `amount`, `payment_method`, `status`, `created_at`)
VALUES 
(1, 'txn_123456', 5000.00, 'razorpay', 'success', '2023-10-01 10:00:00'),
(1, 'txn_789012', 2500.00, 'payu', 'pending', '2023-10-02 11:30:00');

INSERT INTO `admission_form` (`student_id`, `documents`)
VALUES 
(1, '{"aadhar": "aadhar_card.pdf", "marksheet": "marksheet.pdf"}');

-- Insert a user
INSERT INTO `users` (`user_id`, `role`, `email`, `password_hash`, `first_name`, `last_name`, `phone`)
VALUES (1, 'student', 'student@example.com', 'hashed_password', 'John', 'Doe', '1234567890');


-- Insert admission form data
INSERT INTO `admission_form` (`student_id`, `personal_details`, `academic_details`)
VALUES (1, '{"first_name": "John", "last_name": "Doe", "dob": "2000-01-01", "gender": "male", "address": "123 Main St"}', '{"high_school": "ABC School", "year_of_passing": 2020, "marks": 85}');


-- Insert FAQs
INSERT INTO `faqs` (`question`, `answer`)
VALUES 
('How do I apply for admission?', 'You can apply for admission by filling out the admission form on the website.'),
('What is the admission fee?', 'The admission fee is ₹500.');

-- Insert News & Events
INSERT INTO `news_events` (`title`, `description`, `event_date`)
VALUES 
('Admission Open for 2023', 'Admissions are now open for the academic year 2023-2024.', '2023-10-01'),
('Scholarship Program', 'Apply for the scholarship program by November 30, 2023.', '2023-11-30');

