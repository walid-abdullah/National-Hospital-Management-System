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
    header("Location: doctors.php");
    exit();
}

// Fetch doctor data
$stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    $_SESSION['error'] = "Doctor not found.";
    header("Location: doctors.php");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $phone = $_POST['phone'];
    $consultation_fee = $_POST['consultation_fee'];
    $base_salary = $_POST['base_salary'];
    
    $update_stmt = $conn->prepare("UPDATE doctors SET name = ?, specialization = ?, phone = ?, consultation_fee = ?, base_salary = ? WHERE id = ?");
    
    if ($update_stmt->execute([$name, $specialization, $phone, $consultation_fee, $base_salary, $id])) {
        $_SESSION['success'] = "Doctor updated successfully.";
        header("Location: doctors.php");
        exit();
    } else {
        $error = "Failed to update doctor.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - NHIMS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="flex flex-1">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 flex justify-center items-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Doctor</h2>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_doctor.php?id=<?= $id ?>" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($doctor['name']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Specialization</label>
                        <input type="text" name="specialization" value="<?= htmlspecialchars($doctor['specialization']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($doctor['phone']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Consultation Fee (৳)</label>
                        <input type="number" step="0.01" name="consultation_fee" value="<?= htmlspecialchars($doctor['consultation_fee']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Base Salary (৳)</label>
                        <input type="number" step="0.01" name="base_salary" value="<?= htmlspecialchars($doctor['base_salary']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="doctors.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cancel</a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        </main>
    </div>
</body>
</html>
