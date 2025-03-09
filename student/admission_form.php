<?php
// student/admission_form.php

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

// Fetch existing form data (if any)
$sql = "SELECT * FROM admission_form WHERE student_id = ?";
$form = fetchSingle($sql, [$student['student_id']]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = intval($_POST['step']);
    $student_id = $student['student_id'];

    // Prepare form data
    $personal_details = json_encode([
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'dob' => $_POST['dob'],
        'gender' => $_POST['gender'],
        'address' => $_POST['address'],
    ]);

    $academic_details = json_encode([
        'high_school' => $_POST['high_school'],
        'year_of_passing' => $_POST['year_of_passing'],
        'marks' => $_POST['marks'],
    ]);

    $documents = json_encode([
        'aadhar' => $_FILES['aadhar']['name'],
        'marksheet' => $_FILES['marksheet']['name'],
    ]);

    // Save or update form data
    if ($form) {
        $sql = "UPDATE admission_form SET step = ?, personal_details = ?, academic_details = ?, documents = ? WHERE student_id = ?";
        executeQuery($sql, [$step, $personal_details, $academic_details, $documents, $student_id]);
    } else {
        $sql = "INSERT INTO admission_form (student_id, step, personal_details, academic_details, documents) VALUES (?, ?, ?, ?, ?)";
        executeQuery($sql, [$student_id, $step, $personal_details, $academic_details, $documents]);
    }

    // Handle file uploads
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    move_uploaded_file($_FILES['aadhar']['tmp_name'], $upload_dir . $_FILES['aadhar']['name']);
    move_uploaded_file($_FILES['marksheet']['tmp_name'], $upload_dir . $_FILES['marksheet']['name']);

    // Redirect to the next step or confirmation page
    if ($step === 3) {
        header('Location: status.php');
        exit();
    } else {
        header('Location: admission_form.php?step=' . ($step + 1));
        exit();
    }
}

// Determine the current step
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Admission Form</h1>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between">
                <div class="w-1/3 text-center">
                    <span class="block h-2 bg-<?= $current_step >= 1 ? 'blue' : 'gray' ?>-500"></span>
                    <p class="mt-2">Step 1: Personal Details</p>
                </div>
                <div class="w-1/3 text-center">
                    <span class="block h-2 bg-<?= $current_step >= 2 ? 'blue' : 'gray' ?>-500"></span>
                    <p class="mt-2">Step 2: Academic Details</p>
                </div>
                <div class="w-1/3 text-center">
                    <span class="block h-2 bg-<?= $current_step >= 3 ? 'blue' : 'gray' ?>-500"></span>
                    <p class="mt-2">Step 3: Documents</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            <input type="hidden" name="step" value="<?= $current_step ?>">

            <?php if ($current_step === 1): ?>
                <!-- Step 1: Personal Details -->
                <h2 class="text-xl font-bold mb-4">Personal Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-gray-700">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="last_name" class="block text-gray-700">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="dob" class="block text-gray-700">Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="gender" class="block text-gray-700">Gender</label>
                        <select name="gender" id="gender" class="w-full px-3 py-2 border rounded-lg" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="address" class="block text-gray-700">Address</label>
                        <textarea name="address" id="address" class="w-full px-3 py-2 border rounded-lg" required></textarea>
                    </div>
                </div>
            <?php elseif ($current_step === 2): ?>
                <!-- Step 2: Academic Details -->
                <h2 class="text-xl font-bold mb-4">Academic Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="high_school" class="block text-gray-700">High School</label>
                        <input type="text" name="high_school" id="high_school" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="year_of_passing" class="block text-gray-700">Year of Passing</label>
                        <input type="number" name="year_of_passing" id="year_of_passing" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="marks" class="block text-gray-700">Marks (%)</label>
                        <input type="number" name="marks" id="marks" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                </div>
            <?php elseif ($current_step === 3): ?>
                <!-- Step 3: Documents -->
                <h2 class="text-xl font-bold mb-4">Upload Documents</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="aadhar" class="block text-gray-700">Aadhar Card</label>
                        <input type="file" name="aadhar" id="aadhar" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="marksheet" class="block text-gray-700">Marksheet</label>
                        <input type="file" name="marksheet" id="marksheet" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between">
                <?php if ($current_step > 1): ?>
                    <a href="admission_form.php?step=<?= $current_step - 1 ?>" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600">Previous</a>
                <?php endif; ?>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                    <?= $current_step === 3 ? 'Submit' : 'Next' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>