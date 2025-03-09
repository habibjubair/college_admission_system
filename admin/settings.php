<?php
// admin/settings.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all admins
$sql = "SELECT a.admin_id, a.role, a.permissions, u.first_name, u.last_name, u.email
        FROM admins a
        JOIN users u ON a.user_id = u.user_id
        ORDER BY a.role";
$admins = fetchAll($sql);

// Handle role and permission updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $admin_id = intval($_POST['admin_id']);
    $role = $_POST['role'];
    $permissions = json_encode($_POST['permissions'] ?? []);

    $sql = "UPDATE admins SET role = ?, permissions = ? WHERE admin_id = ?";
    executeQuery($sql, [$role, $permissions, $admin_id]);

    // Redirect to avoid form resubmission
    header('Location: settings.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - College Admission System</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">Admin Settings</h1>

        <!-- Admin List -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Manage Admins</h2>
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2">Permissions</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($admin['email']) ?></td>
                            <td class="px-4 py-2"><?= ucfirst($admin['role']) ?></td>
                            <td class="px-4 py-2">
                                <?php
                                $permissions = json_decode($admin['permissions'], true);
                                if (is_array($permissions)) {
                                    echo implode(', ', $permissions);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td class="px-4 py-2">
                                <button onclick="openModal('editAdminModal', <?= $admin['admin_id'] ?>, '<?= $admin['role'] ?>', <?= htmlspecialchars(json_encode(json_decode($admin['permissions'], true))) ?>)" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div id="editAdminModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4">
        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Edit Admin</h2>
            <form method="POST">
                <input type="hidden" name="admin_id" id="editAdminId">
                <div class="mb-4">
                    <label for="editRole" class="block text-gray-700">Role</label>
                    <select name="role" id="editRole" class="w-full px-3 py-2 border rounded-lg" required>
                        <option value="super_admin">Super Admin</option>
                        <option value="admission_officer">Admission Officer</option>
                        <option value="accountant">Accountant</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Permissions</label>
                    <div>
                        <label><input type="checkbox" name="permissions[]" value="manage_students"> Manage Students</label>
                    </div>
                    <div>
                        <label><input type="checkbox" name="permissions[]" value="manage_payments"> Manage Payments</label>
                    </div>
                    <div>
                        <label><input type="checkbox" name="permissions[]" value="generate_reports"> Generate Reports</label>
                    </div>
                </div>
                <button type="submit" name="update_admin" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Update</button>
            </form>
            <button onclick="closeModal('editAdminModal')" class="w-full bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 mt-2">Cancel</button>
        </div>
    </div>

    <!-- JavaScript for Modal -->
    <script>
        function openModal(modalId, adminId, role, permissions) {
            document.getElementById('editAdminId').value = adminId;
            document.getElementById('editRole').value = role;

            // Set permissions checkboxes
            const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = permissions.includes(checkbox.value);
            });

            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>