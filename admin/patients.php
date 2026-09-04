<?php
require_once '../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';
$per_page = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$like = '%' . $search . '%';

// Delete logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Invalid security token.";
        header("Location: patients.php");
        exit();
    }
    $del_id = (int) $_POST['delete_id'];
    try {
        // Find user_id before deleting patient
        $stmt_u = $conn->prepare("SELECT user_id FROM patients WHERE id = ?");
        $stmt_u->execute([$del_id]);
        $pat = $stmt_u->fetch();
        if ($pat) {
            $conn->prepare("DELETE FROM patients WHERE id = ?")->execute([$del_id]);
            $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$pat['user_id']]);
            $_SESSION['success'] = "Patient deleted successfully!";
        }
        header("Location: patients.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Cannot delete patient. They might have active appointments or records.";
    }
}

// Fetch all patients with hospital name
try {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM patients p JOIN hospitals h ON p.hospital_id = h.id
        WHERE p.name LIKE :name_search OR p.phone LIKE :phone_search OR p.blood_group LIKE :blood_search OR h.name LIKE :hospital_search");
    $count_stmt->execute([
        ':name_search' => $like,
        ':phone_search' => $like,
        ':blood_search' => $like,
        ':hospital_search' => $like,
    ]);
    $total_items = (int) $count_stmt->fetchColumn();
    $total_pages = max(1, (int) ceil($total_items / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;

    $query = "SELECT p.id, p.name, p.age, p.gender, p.blood_group, p.phone, h.name AS hospital_name
              FROM patients p 
              JOIN hospitals h ON p.hospital_id = h.id 
              WHERE p.name LIKE :name_search OR p.phone LIKE :phone_search OR p.blood_group LIKE :blood_search OR h.name LIKE :hospital_search
              ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':name_search', $like, PDO::PARAM_STR);
    $stmt->bindValue(':phone_search', $like, PDO::PARAM_STR);
    $stmt->bindValue(':blood_search', $like, PDO::PARAM_STR);
    $stmt->bindValue(':hospital_search', $like, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching patients: " . $e->getMessage();
    $total_items = 0;
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients - NHIMS</title>
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
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-6xl mx-auto animate-fade-in-up p-6 mt-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Patient Management</h2>
            <a href="add_patient.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 px-5 py-2 rounded-lg font-medium shadow transition">
                + Add New Patient
            </a>
        </div>
        <form method="GET" class="mb-6 flex flex-col sm:flex-row gap-3">
            <input type="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by name, phone, blood group, or hospital..." class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold">Search</button>
            <?php if ($search !== ''): ?><a href="patients.php" class="px-5 py-2.5 rounded-xl bg-gray-200 text-gray-700 font-semibold text-center">Clear</a><?php endif; ?>
        </form>

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
                        <th class="p-4 border-b">Patient Name</th>
                        <th class="p-4 border-b">Age/Gender</th>
                        <th class="p-4 border-b">Blood Group</th>
                        <th class="p-4 border-b">Hospital</th>
                        <th class="p-4 border-b">Phone</th>
                        <th class="p-4 border-b text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(!empty($patients)): ?>
                        <?php foreach($patients as $patient): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold text-gray-700 dark:text-gray-200">#<?php echo $patient['id']; ?></td>
                            <td class="p-4 text-sm font-medium text-blue-600"><?php echo htmlspecialchars($patient['name']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($patient['age']) . ' / ' . htmlspecialchars($patient['gender']); ?></td>
                            <td class="p-4 text-sm">
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded font-bold"><?php echo htmlspecialchars($patient['blood_group']); ?></span>
                            </td>
                            <td class="p-4 text-sm font-medium"><?php echo htmlspecialchars($patient['hospital_name']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($patient['phone']); ?></td>
                            <td class="p-4 text-center space-x-2">
                                <a href="edit_patient.php?id=<?php echo $patient['id']; ?>" class="text-blue-500 hover:text-blue-700 font-medium text-sm">Edit</a>
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this patient?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="delete_id" value="<?php echo (int) $patient['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500 dark:text-gray-400">No patients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-6">
            <span class="text-sm text-gray-500 dark:text-gray-400">Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_items; ?> patients)</span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?><a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold">Prev</a><?php endif; ?>
                <?php if ($page < $total_pages): ?><a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">Next</a><?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Information Management System. Designed for Software Engineering Project.
    </footer>

</body>

</html>
