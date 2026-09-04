<?php
require_once __DIR__ . '/../includes/security.php';
init_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../login.php");
    exit();
}
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id AS patient_id FROM patients WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($patient) {
    $patient_id = $patient['patient_id'];
    $query = "SELECT l.id as test_id, s.service_name as test_name, l.status, l.result_text as test_result, l.test_date, l.file_path 
              FROM laboratory_tests l
              JOIN lab_services s ON l.service_id = s.id
              WHERE l.patient_id = :patient_id 
              ORDER BY l.test_date DESC";
    $stmt2 = $conn->prepare($query);
    $stmt2->bindParam(':patient_id', $patient_id);
    $stmt2->execute();
    $tests = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $tests = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Lab Reports - NHIMS</title>
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
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Patient Portal</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Dashboard</a>
            <a href="appointments.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Appointments</a>
            <a href="records.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">History</a>
            <a href="prescriptions.php" class="text-teal-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Prescriptions</a>
            <a href="lab_tests.php" class="text-blue-600 font-bold transition">Lab Reports</a>
            <span class="border-l border-teal-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto w-full mt-10 p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-100">Laboratory Test Results</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(!empty($tests)): ?>
                <?php foreach($tests as $test): ?>
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 transition-transform relative">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-lg text-purple-700 dark:text-purple-400"><?php echo htmlspecialchars($test['test_name']); ?></h3>
                        <span class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($test['test_date'])); ?></span>
                    </div>
                    
                    <div class="mb-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded 
                            <?php 
                                if($test['status'] === 'Completed') echo 'bg-green-100 text-green-700'; 
                                else if($test['status'] === 'Processing') echo 'bg-blue-100 text-blue-700';
                                else echo 'bg-yellow-100 text-yellow-700'; 
                            ?>">
                            <?php echo htmlspecialchars($test['status']); ?>
                        </span>
                    </div>

                    <div class="mb-4 mt-4">
                        <p class="text-sm text-gray-500 mb-1">Result / Report:</p>
                        <?php if($test['status'] !== 'Completed'): ?>
                            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 rounded-lg text-sm border border-yellow-200 dark:border-yellow-800">
                                This test result is currently <?php echo strtolower($test['status']); ?> by the lab staff. Please check back later.
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200 rounded-lg text-sm border border-gray-200 dark:border-slate-700 shadow-inner min-h-[80px] whitespace-pre-wrap">
                                <?php echo htmlspecialchars($test['test_result'] ?? 'No text report provided.'); ?>
                            </div>
                            <?php if(!empty($test['file_path'])): ?>
                                <a href="../<?php echo htmlspecialchars($test['file_path']); ?>" target="_blank" class="mt-4 block text-center bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition font-bold shadow-md">Download Attached PDF</a>
                            <?php else: ?>
                                <a href="print_lab_report.php?id=<?php echo $test['test_id']; ?>" target="_blank" class="mt-4 block text-center bg-purple-600 hover:bg-purple-700 text-white py-2 rounded transition font-bold shadow-md">Generate & Download PDF</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full p-8 text-center text-gray-500 glass rounded-2xl">
                    No lab tests booked or found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
