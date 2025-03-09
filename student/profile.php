<?php
// student/profile.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch student details
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.student_id, s.admission_status, s.admission_date, s.payment_status, s.course_id,
               u.first_name, u.last_name, u.email, u.phone, c.course_name
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN courses c ON s.course_id = c.course_id
        WHERE s.user_id = ?";
$student = fetchSingle($sql, [$user_id]);

// Fetch admission form data (if any)
$sql = "SELECT personal_details, academic_details FROM admission_form WHERE student_id = ?";
$form = fetchSingle($sql, [$student['student_id']]);
$personal_details = $form ? json_decode($form['personal_details'], true) : [];
$academic_details = $form ? json_decode($form['academic_details'], true) : [];

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $dob = trim($_POST['dob']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    $high_school = trim($_POST['high_school']);
    $year_of_passing = trim($_POST['year_of_passing']);
    $marks = trim($_POST['marks']);

    // Update user details
    $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE user_id = ?";
    executeQuery($sql, [$first_name, $last_name, $phone, $user_id]);

    // Update admission form details
    $personal_details = json_encode([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'dob' => $dob,
        'gender' => $gender,
        'address' => $address,
    ]);

    $academic_details = json_encode([
        'high_school' => $high_school,
        'year_of_passing' => $year_of_passing,
        'marks' => $marks,
    ]);

    $sql = "UPDATE admission_form SET personal_details = ?, academic_details = ? WHERE student_id = ?";
    executeQuery($sql, [$personal_details, $academic_details, $student['student_id']]);

    // Redirect to avoid form resubmission
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Student Profile</h1>

        <!-- Profile Form -->
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Personal Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                </div>
                <div>
                    <label for="last_name" class="block text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                </div>
                <div>
                    <label for="phone" class="block text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($student['phone']) ?>">
                </div>
                <div>
                    <label for="dob" class="block text-gray-700">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($personal_details['dob'] ?? '') ?>">
                </div>
                <div>
                    <label for="gender" class="block text-gray-700">Gender</label>
                    <select name="gender" id="gender" class="w-full px-3 py-2 border rounded-lg">
                        <option value="male" <?= ($personal_details['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($personal_details['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= ($personal_details['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="address" class="block text-gray-700">Address</label>
                    <textarea name="address" id="address" class="w-full px-3 py-2 border rounded-lg"><?= htmlspecialchars($personal_details['address'] ?? '') ?></textarea>
                </div>
            </div>

            <h2 class="text-xl font-bold mb-4 mt-8">Academic Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="high_school" class="block text-gray-700">High School</label>
                    <input type="text" name="high_school" id="high_school" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($academic_details['high_school'] ?? '') ?>">
                </div>
                <div>
                    <label for="year_of_passing" class="block text-gray-700">Year of Passing</label>
                    <input type="number" name="year_of_passing" id="year_of_passing" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($academic_details['year_of_passing'] ?? '') ?>">
                </div>
                <div>
                    <label for="marks" class="block text-gray-700">Marks (%)</label>
                    <input type="number" name="marks" id="marks" class="w-full px-3 py-2 border rounded-lg" value="<?= htmlspecialchars($academic_details['marks'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 mt-8">Update Profile</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>