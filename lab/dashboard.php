<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Laboratory Staff') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "SELECT l.test_id, p.name AS patient_name, l.test_name, l.test_result, l.test_date 
          FROM laboratory_tests l 
          JOIN patients p ON l.patient_id = p.patient_id ";

if ($filter === 'pathology') {
    $query .= " WHERE l.test_name IN ('Complete Blood Count (CBC)', 'Lipid Profile', 'Thyroid Test (TSH)', 'Blood Glucose (Fasting)', 'Urine Routine', 'Liver Function Test', 'Kidney Function Test') ";
} elseif ($filter === 'radiology') {
    $query .= " WHERE l.test_name IN ('MRI Scan', 'Digital X-Ray') ";
} elseif ($filter === 'cardiology') {
    $query .= " WHERE l.test_name IN ('ECG / EKG') ";
}

$query .= " ORDER BY l.test_date DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching lab tests: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Dashboard - NHIMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Laboratory</h1>
        <div class="flex items-center space-x-4">
            <span class="border-l border-yellow-400 h-6 mx-2"></span>
            <span>Welcome, Lab Tech <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>
    
    <div class="max-w-6xl mx-auto p-6 mt-6 w-full">
        <h2 class="text-3xl font-extrabold mb-8 text-gray-800 dark:text-gray-100 text-center">Select Department</h2>
        
        <!-- Department Filter Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <a href="dashboard.php?filter=all" class="<?php echo $filter=='all' ? 'ring-4 ring-blue-500 scale-105' : 'hover:-translate-y-1'; ?> glass p-6 rounded-2xl shadow border border-white/50 transition-all text-center">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">All Departments</h3>
                <p class="text-sm text-gray-500 mt-2">View central queue</p>
            </a>
            <a href="dashboard.php?filter=pathology" class="<?php echo $filter=='pathology' ? 'ring-4 ring-red-500 scale-105' : 'hover:-translate-y-1'; ?> glass p-6 rounded-2xl shadow border border-white/50 transition-all text-center">
                <h3 class="text-xl font-bold text-red-600 dark:text-red-400">Pathology</h3>
                <p class="text-sm text-gray-500 mt-2">Blood & Fluids</p>
            </a>
            <a href="dashboard.php?filter=radiology" class="<?php echo $filter=='radiology' ? 'ring-4 ring-blue-500 scale-105' : 'hover:-translate-y-1'; ?> glass p-6 rounded-2xl shadow border border-white/50 transition-all text-center">
                <h3 class="text-xl font-bold text-blue-600 dark:text-blue-400">Radiology</h3>
                <p class="text-sm text-gray-500 mt-2">X-Ray & MRI</p>
            </a>
            <a href="dashboard.php?filter=cardiology" class="<?php echo $filter=='cardiology' ? 'ring-4 ring-green-500 scale-105' : 'hover:-translate-y-1'; ?> glass p-6 rounded-2xl shadow border border-white/50 transition-all text-center">
                <h3 class="text-xl font-bold text-green-600 dark:text-green-400">Cardiology</h3>
                <p class="text-sm text-gray-500 mt-2">ECG & Heart</p>
            </a>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">Test Requests Queue</h2>
        <div class="glass rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">Patient Name</th>
                        <th class="p-4 border-b">Test Name</th>
                        <th class="p-4 border-b">Result Status</th>
                        <th class="p-4 border-b">Date</th>
                        <th class="p-4 border-b text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php if(!empty($tests)): ?>
                        <?php foreach($tests as $test): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold">#<?php echo $test['test_id']; ?></td>
                            <td class="p-4 text-sm font-medium text-gray-800 dark:text-gray-200"><?php echo htmlspecialchars($test['patient_name']); ?></td>
                            <td class="p-4 text-sm font-bold text-yellow-700 dark:text-yellow-500"><?php echo htmlspecialchars($test['test_name']); ?></td>
                            <td class="p-4 text-sm text-gray-600 dark:text-gray-300">
                                <?php if(strpos(strtolower($test['test_result']), 'pending') !== false): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                        <?php echo htmlspecialchars($test['test_result']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        Completed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo date('M d, Y', strtotime($test['test_date'])); ?></td>
                            <td class="p-4 text-sm text-center">
                                <a href="update_test.php?id=<?php echo $test['test_id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded shadow text-xs font-bold transition">Update Result</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">No lab tests found for this category.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
