<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: appointments.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: appointments.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    $appointment_date = $_POST['appointment_date'];
    $appointment_type = $_POST['appointment_type'];
    $status = $_POST['status'];
    $meeting_link = $_POST['meeting_link'] ?? null;
    
    $update_stmt = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_type = ?, status = ?, meeting_link = ? WHERE id = ?");
    
    if ($update_stmt->execute([$appointment_date, $appointment_type, $status, $meeting_link, $id])) {
        $_SESSION['success'] = "Appointment updated successfully.";
        header("Location: appointments.php");
        exit();
    } else {
        $error = "Failed to update appointment.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col min-h-screen">
    <?php include 'includes/navbar.php'; ?>

    <div class="flex flex-1">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 flex justify-center items-start pt-12">
            <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-lg w-full max-w-lg border border-gray-100 dark:border-slate-700">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Appointment</h2>
                    <a href="appointments.php" class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">Back</a>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit_appointment.php?id=<?= $id ?>" class="space-y-5">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Appointment Date</label>
                        <input type="date" name="appointment_date" value="<?= htmlspecialchars($appointment['appointment_date']) ?>" required class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                    </div>
                    
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Type</label>
                            <select name="appointment_type" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                                <option value="Physical" <?= $appointment['appointment_type'] === 'Physical' ? 'selected' : '' ?>>Physical</option>
                                <option value="Telemedicine" <?= $appointment['appointment_type'] === 'Telemedicine' ? 'selected' : '' ?>>Telemedicine</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                                <option value="Pending" <?= $appointment['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Confirmed" <?= $appointment['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="Completed" <?= $appointment['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $appointment['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Meeting Link (For Telemedicine)</label>
                        <input type="url" name="meeting_link" value="<?= htmlspecialchars($appointment['meeting_link'] ?? '') ?>" placeholder="https://zoom.us/j/..." class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="appointments.php" class="px-5 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Cancel</a>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
