<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: medical_records.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM medical_records WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "Medical record not found.";
    header("Location: medical_records.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $visit_date = $_POST['visit_date'];
    
    $update_stmt = $conn->prepare("UPDATE medical_records SET diagnosis = ?, treatment = ?, visit_date = ? WHERE id = ?");
    
    if ($update_stmt->execute([$diagnosis, $treatment, $visit_date, $id])) {
        $_SESSION['success'] = "Medical record updated successfully.";
        header("Location: medical_records.php");
        exit();
    } else {
        $error = "Failed to update medical record.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medical Record - NHIMS</title>
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
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Medical Record</h2>
                    <a href="medical_records.php" class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">Back</a>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit_medical_record.php?id=<?= $id ?>" class="space-y-5">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Visit Date</label>
                        <input type="date" name="visit_date" value="<?= htmlspecialchars($record['visit_date']) ?>" required class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Diagnosis</label>
                        <textarea name="diagnosis" rows="3" required class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white"><?= htmlspecialchars($record['diagnosis']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Treatment / Prescription</label>
                        <textarea name="treatment" rows="4" required class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-200 dark:bg-slate-900 dark:border-slate-700 dark:text-white"><?= htmlspecialchars($record['treatment']) ?></textarea>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="medical_records.php" class="px-5 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Cancel</a>
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
