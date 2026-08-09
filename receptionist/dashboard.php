<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Receptionist') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard - NHIMS</title>
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
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHIMS - Reception Desk</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-purple-200 hover:text-gray-600 dark:text-gray-300 hover:text-blue-600 transition font-medium">Dashboard</a>
            <span class="border-l border-purple-400 h-6 mx-2"></span>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow">Logout</a>
        </div>
    </nav>
    <div class="max-w-5xl mx-auto animate-fade-in-up mt-10">
        <div class="glass p-8 rounded-3xl shadow-2xl border border-white/50">
            <h2 class="text-2xl font-bold mb-4">Receptionist Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-8">Manage patient registrations and daily appointments.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-purple-50 p-6 rounded-lg border border-purple-100">
                    <h3 class="text-xl font-semibold text-purple-800">Patient List & Registration</h3>
                    <p class="text-sm text-purple-600 mt-2">View all patients or register offline patients.</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="patients.php" class="inline-block bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 px-4 py-2 rounded">View Patients</a>
                        <a href="../register.php" target="_blank" class="inline-block bg-white text-purple-700 border border-purple-200 shadow hover:shadow-md transition-all duration-200 px-4 py-2 rounded">Offline Reg.</a>
                    </div>
                </div>
                <div class="bg-pink-50 p-6 rounded-lg border border-pink-100">
                    <h3 class="text-xl font-semibold text-pink-800">Queue Management</h3>
                    <p class="text-sm text-pink-600 mt-2">Manage appointments and queue status.</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="appointments.php" class="inline-block bg-pink-600 text-white px-4 py-2 rounded shadow hover:bg-pink-700">Manage Queue</a>
                        <a href="appointments.php?quick_book=1" class="inline-block bg-white text-pink-700 border border-pink-200 shadow hover:shadow-md px-4 py-2 rounded">Quick Book</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-auto py-6 text-center text-gray-500 dark:text-gray-400 dark:text-gray-400 text-sm border-t border-gray-200 dark:border-slate-700 dark:border-gray-800 w-full glass">
        &copy; 2026 National Hospital Information Management System. Designed for Software Engineering Project.
    </footer>
</body>

</html>
