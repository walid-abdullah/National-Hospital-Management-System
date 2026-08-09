<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Get doctor id
$stmt = $conn->prepare("SELECT id as doctor_id FROM doctors WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doctor) {
    $doctor_id = $doctor['doctor_id'];
    // Get records
    $query = "SELECT m.record_id, p.name AS patient_name, m.diagnosis, m.treatment, m.visit_date 
              FROM medical_records m 
              JOIN patients p ON m.patient_id = p.id 
              WHERE m.doctor_id = :doctor_id 
              ORDER BY m.visit_date DESC";
    $stmt2 = $conn->prepare($query);
    $stmt2->bindParam(':doctor_id', $doctor_id);
    $stmt2->execute();
    $records = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    $records = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Doctor Portal</title>
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
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm text-gray-800 dark:text-gray-100 flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Doctor Portal</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-blue-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Dashboard</a>
            <a href="appointments.php" class="text-blue-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Appointments</a>
            <span class="border-l border-blue-400 h-6 mx-2"></span>
            <span>Dr. <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow">Logout</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto animate-fade-in-up mt-10 p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">My Patients' Medical Records</h2>
        </div>

        <div class="glass rounded-2xl shadow-xl border border-white/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <th class="p-4 border-b">Rec ID</th>
                        <th class="p-4 border-b">Patient Name</th>
                        <th class="p-4 border-b">Visit Date</th>
                        <th class="p-4 border-b">Diagnosis</th>
                        <th class="p-4 border-b">Treatment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(!empty($records)): ?>
                        <?php foreach($records as $rec): ?>
                        <tr class="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200">
                            <td class="p-4 text-sm font-semibold text-gray-700 dark:text-gray-200">#<?php echo $rec['record_id']; ?></td>
                            <td class="p-4 text-sm font-medium"><?php echo htmlspecialchars($rec['patient_name']); ?></td>
                            <td class="p-4 text-sm text-gray-500 dark:text-gray-400"><?php echo date('M d, Y', strtotime($rec['visit_date'])); ?></td>
                            <td class="p-4 text-sm text-red-600 font-medium"><?php echo htmlspecialchars($rec['diagnosis']); ?></td>
                            <td class="p-4 text-sm text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($rec['treatment']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500 dark:text-gray-400">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Information Management System. Designed for Software Engineering Project.
    </footer>
</body>

</html>
