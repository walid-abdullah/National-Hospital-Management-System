<?php
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') header("Location: admin/dashboard.php");
    elseif ($_SESSION['role'] === 'Doctor') header("Location: doctor/dashboard.php");
    elseif ($_SESSION['role'] === 'Patient') header("Location: patient/dashboard.php");
    elseif ($_SESSION['role'] === 'Receptionist') header("Location: receptionist/dashboard.php");
    elseif ($_SESSION['role'] === 'Laboratory Staff') header("Location: lab/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NHIMS - Login</title>
    <!-- Tailwind CSS -->
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
        .dark .glass { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); }
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .custom-gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <script>
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
<body class="min-h-screen flex flex-col bg-slate-50 dark:bg-gray-900 transition-colors duration-300 antialiased selection:bg-blue-200 selection:text-blue-900 relative">
    
    <div class="absolute top-4 right-4 z-50">
        <button onclick="toggleDarkMode()" class="p-2 rounded-full glass hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200 text-gray-600 dark:text-gray-300 shadow-md" title="Toggle Dark Mode">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
        </button>
    </div>

    <div class="flex-grow flex items-center justify-center p-4">
        <div class="glass p-8 rounded-3xl shadow-2xl w-full max-w-md text-gray-800 dark:text-gray-100 animate-fade-in-up border border-white/50 dark:border-white/10 relative overflow-hidden">
            <!-- Decorative gradient orb -->
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-400 dark:bg-blue-600 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-30 animate-pulse"></div>
            
            <div class="text-center mb-8 relative z-10">
                <h1 class="text-4xl font-extrabold mb-2 custom-gradient-text tracking-tight">NHIMS</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">National Hospital Information Management System</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 rounded mb-6 text-sm flex items-center relative z-10 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="login_action.php" method="POST" class="space-y-5 relative z-10">
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                    <input type="text" id="username" name="username" required 
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100"
                        placeholder="Enter your username">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100"
                        placeholder="••••••••">
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Login As</label>
                    <select id="role" name="role" required 
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-inner text-gray-800 dark:text-gray-100 appearance-none">
                        <option value="" disabled selected>Select your role</option>
                        <option value="Admin">Admin</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Receptionist">Receptionist</option>
                        <option value="Laboratory Staff">Laboratory Staff</option>
                        <option value="Pharmacist">Pharmacist</option>
                        <option value="Patient">Patient</option>
                    </select>
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-0.5 mt-6">
                    Sign In
                </button>

                <div class="mt-4 text-center">
                    <a href="register.php" class="text-sm text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
                        New here? Create an account
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer class="py-6 text-center text-gray-500 dark:text-gray-500 text-sm w-full border-t border-gray-200 dark:border-slate-800/50 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md">
        &copy; <?php echo date('Y'); ?> National Hospital Information Management System. All rights reserved.
    </footer>
</body>
</html>
