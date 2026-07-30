<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

try {
    // Fetch patients
    $stmtP = $conn->prepare("SELECT patient_id, name FROM patients ORDER BY name ASC");
    $stmtP->execute();
    $patients = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    // Fetch doctors
    $stmtD = $conn->prepare("SELECT doctor_id, doctor_name, specialization FROM doctors ORDER BY doctor_name ASC");
    $stmtD->execute();
    $doctors = $stmtD->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error loading data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - NHMS</title>
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
            <a href="appointments.php" class="text-blue-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition">Back to Appointments</a>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow">Logout</a>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto animate-fade-in-up p-6 mt-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md p-8 border border-gray-100 dark:border-slate-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">Book New Appointment</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="add_appointment_action.php" method="POST" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Patient -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Select Patient</label>
                        <select name="patient_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="" disabled selected>-- Select Patient --</option>
                            <?php foreach($patients as $p): ?>
                                <option value="<?php echo $p['patient_id']; ?>"><?php echo htmlspecialchars($p['name']); ?> (ID: <?php echo $p['patient_id']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Doctor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Select Doctor</label>
                        <select name="doctor_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="" disabled selected>-- Select Doctor --</option>
                            <?php foreach($doctors as $d): ?>
                                <option value="<?php echo $d['doctor_id']; ?>"><?php echo htmlspecialchars($d['doctor_name']); ?> (<?php echo htmlspecialchars($d['specialization']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Appointment Date</label>
                        <input type="date" name="appointment_date" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Status</label>
                        <select name="status" required class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="Pending" selected>Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 px-6 py-2 rounded-lg font-medium shadow-lg transition">Book Appointment</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Management System. Designed for Software Engineering Project.
    </footer>
</body>

</html>
