<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: patients.php");
    exit();
}

// Fetch patient data
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    $_SESSION['error'] = "Patient not found.";
    header("Location: patients.php");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $blood_group = $_POST['blood_group'];
    
    $update_stmt = $conn->prepare("UPDATE patients SET name = ?, age = ?, gender = ?, phone = ?, address = ?, blood_group = ? WHERE id = ?");
    
    if ($update_stmt->execute([$name, $age, $gender, $phone, $address, $blood_group, $id])) {
        $_SESSION['success'] = "Patient updated successfully.";
        header("Location: patients.php");
        exit();
    } else {
        $error = "Failed to update patient.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - NHIMS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <div class="flex flex-1">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 flex justify-center items-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Patient</h2>
            
            <?php if(isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_patient.php?id=<?= $id ?>" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($patient['name']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Age</label>
                        <input type="number" name="age" value="<?= htmlspecialchars($patient['age']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Gender</label>
                        <select name="gender" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="Male" <?= $patient['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $patient['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $patient['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Blood Group</label>
                        <input type="text" name="blood_group" value="<?= htmlspecialchars($patient['blood_group'] ?? '') ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Address</label>
                    <textarea name="address" rows="3" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= htmlspecialchars($patient['address']) ?></textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="patients.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cancel</a>
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
