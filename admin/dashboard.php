<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NHMS</title>
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


<body class="bg-slate-50 antialiased selection:bg-blue-200 selection:text-blue-900 flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <!-- Navbar -->
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm text-gray-800 dark:text-gray-100 flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHMS Admin</h1>
        <div class="flex items-center space-x-4">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-slate-800 min-h-screen shadow-lg hidden md:block">
            <ul class="p-4 space-y-2">
                <li><a href="dashboard.php" class="block p-3 bg-blue-50 text-blue-700 rounded-lg font-semibold">Dashboard</a></li>
                <li><a href="doctors.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Manage Doctors</a></li>
                <li><a href="patients.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Manage Patients</a></li>
                <li><a href="appointments.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Appointments</a></li>
                <li><a href="medical_records.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Medical Records</a></li>
                <li><a href="lab_tests.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Lab Tests</a></li>
                <li><a href="billing.php" class="block p-3 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-700 rounded-lg transition">Billing</a></li>
            </ul>
        </aside>

        <!-- Dashboard Content -->
        <main class="flex-1 p-8 animate-fade-in-up">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Doctors</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">12</p>
                </div>
                <!-- Card 2 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Patients</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">154</p>
                </div>
                <!-- Card 3 -->
                <div class="glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">Today's Appointments</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-2">24</p>
                </div>
            </div>
            
            <div class="mt-8 glass p-6 rounded-2xl shadow-lg border border-white/50 hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Recent System Activities</h3>
                <p class="text-gray-500 dark:text-gray-400">System is running normally. All modules (Patients, Doctors, Appointments, Lab Tests, Billing) are fully functional. Manage records from the left menu.</p>
            </div>
        </main>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Management System. Designed for Software Engineering Project.
    </footer>
</body>

</html>
