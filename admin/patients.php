<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

// Delete logic
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    try {
        // Find user_id before deleting patient
        $stmt_u = $conn->prepare("SELECT user_id FROM patients WHERE patient_id = ?");
        $stmt_u->execute([$del_id]);
        $pat = $stmt_u->fetch();
        if ($pat) {
            $conn->prepare("DELETE FROM patients WHERE patient_id = ?")->execute([$del_id]);
            $conn->prepare("DELETE FROM users WHERE user_id = ?")->execute([$pat['user_id']]);
            $_SESSION['success'] = "Patient deleted successfully!";
        }
        header("Location: patients.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Cannot delete patient. They might have active appointments or records.";
    }
}

// Fetch all patients
try {
    $stmt = $conn->prepare("SELECT * FROM patients ORDER BY patient_id DESC");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching patients: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - NHMS</title>
    <script src="https://cdn.tailwindcss.com"></script>

    
    <script>
        tailwind.config = {
          darkMode: 'class',
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <script>
        // Check local storage for dark mode preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</head>


<body class="bg-slate-50 text-slate-800 antialiased selection:bg-blue-200 selection:text-blue-900 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <!-- Navbar -->
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm text-gray-800 dark:text-gray-100 flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHMS Admin</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-blue-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition">Dashboard</a>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto animate-fade-in-up p-6 mt-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Patient Management</h2>
            <a href="add_patient.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 px-5 py-2 rounded-lg font-medium shadow transition">
                + Add New Patient
            </a>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Patients Table -->
        <div class="glass rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">Name</th>
                        <th class="p-4 border-b">Age/Gender</th>
                        <th class="p-4 border-b">Phone</th>
                        <th class="p-4 border-b">Address</th>
                        <th class="p-4 border-b text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(!empty($patients)): ?>
                        <?php foreach($patients as $patient): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold text-gray-700 dark:text-gray-200">#<?php echo $patient['patient_id']; ?></td>
                            <td class="p-4 text-sm font-medium"><?php echo htmlspecialchars($patient['name']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo $patient['age']; ?> Yrs / <?php echo $patient['gender']; ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($patient['phone']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs"><?php echo htmlspecialchars($patient['address']); ?></td>
                            <td class="p-4 text-center space-x-2">
                                <a href="edit_patient.php?id=<?php echo $patient['patient_id']; ?>" class="text-blue-500 hover:text-blue-700 font-medium text-sm">Edit</a>
                                <a href="?delete_id=<?php echo $patient['patient_id']; ?>" onclick="return confirm('Are you sure you want to delete this patient?');" class="text-red-500 hover:text-red-700 font-medium text-sm">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">No patients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Management System. Designed for Software Engineering Project.
    </footer>
</body>

</html>
